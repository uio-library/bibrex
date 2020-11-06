<?php

namespace App\Http\Controllers;

use App\Events\LoanTableUpdated;
use App\Http\Requests\CheckinRequest;
use App\Http\Requests\CheckoutRequest;
use App\Item;
use App\Loan;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Scriptotek\Alma\Client as AlmaClient;
use Scriptotek\Alma\Bibs\Item as AlmaItem;
use Scriptotek\Alma\Exception\RequestFailed;
use function Stringy\create as s;

class LoansController extends Controller
{
    /**
     * Validation error messages.
     *
     * @static array
     */
    protected $messages = [
        'user.required' => 'Trenger enten navn eller lånekortnummer.',
        'user.id.required_without' => 'Trenger enten navn eller lånekortnummer.',
        'user.name.required_without' => 'Trenger enten navn eller lånekortnummer.',
        'thing.required' => 'Uten ting blir det bare ingenting.',
    ];

    /*
     * Factory for Laravel Auth
     */
    protected $auth;

    /*
     * The currently logged in library
     */
    protected $library;

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Response
     */
    public function getIndex(Request $request)
    {
        $user = $request->input('user')
            ? ['name' => $request->input('user')]
            : $request->session()->get('user');

        $thing = $request->input('thing')
            ? ['name' => $request->input('thing')]
            : $request->session()->get('thing');

        return response()->view('loans.index', [
            'library_id' => \Auth::user()->id,
            'user' => $user,
            'thing' => $thing,
        ])->header('Cache-Control', 'private, no-store, no-cache, must-revalidate, max-age=0');
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Response
     */
    public function json()
    {
        $library = \Auth::user();

        $loans = Loan::with('item.thing', 'user', 'user.identifiers', 'notifications')
            ->where('library_id', $library->id)
            ->orderBy('created_at', 'desc')->get();

        return response()->json($loans);
    }

    /**
     * Display the specified resource.
     *
     * @param Loan $loan
     * @return Response
     */
    public function getShow(Loan $loan)
    {
        if ($loan) {
            return response()->view('loans.show', array('loan' => $loan));
        } else {
            return response()->view('errors.404', array('what' => 'Lånet'), 404);
        }
    }


    /**
     * Logs the response and returns it.
     *
     * @param Loan $loan
     * @return Response
     */
    protected function loggedResponse($data)
    {
        if (isset($data['error'])) {
            \Log::info($data['error'], ['library' => \Auth::user()->name]);
            return response()->json($data, 422);
        }
        \Log::info($data['status'], ['library' => \Auth::user()->name]);
        return response()->json($data, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param AlmaClient $alma
     * @param CheckoutRequest $request
     * @return Response
     */
    public function checkout(AlmaClient $alma, CheckoutRequest $request)
    {
        if (is_a($request->item, AlmaItem::class)) {
            return $this->checkoutAlmaItem($alma, $request->item, $request->user, $request);
        }

        // Create new loan
        return $this->checkoutLocalItem($request->item, $request->user, $request);
    }

    public function checkoutLocalItem(Item $item, User $user, Request $request)
    {
        $loan = Loan::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'due_at' => Carbon::now()
                ->addDays($item->thing->properties->loan_time)
                ->setTime(0, 0, 0),
            'as_guest' => false,
        ]);
        if (!$loan) {
            return response()->json(['errors' => $loan->errors], 409);
        }

        $user->loan_count += 1;
        $user->last_loan_at = Carbon::now();
        $user->save();

        event(new LoanTableUpdated('checkout', $request, $loan));

        $loan->load('user', 'item', 'item.thing');

        return $this->loggedResponse([
            'status' => sprintf(
                'Lånte ut %s (<a href="%s">Detaljer</a>).',
                $item->thing->properties->get('name_indefinite.nob'),
                action('LoansController@getShow', $loan->id)
            ),
            'loan' => $loan,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Loan $loan
     * @return Response
     */
    public function edit(Loan $loan)
    {
        return response()->view('loans.edit', ['loan' => $loan]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Loan $loan
     * @param Request $request
     * @return void
     */
    public function update(Loan $loan, Request $request)
    {
        $request->validate([
            'due_at' => 'required|date',
        ]);

        $oldDate = $loan->due_at;

        $loan->due_at = Carbon::parse($request->due_at);
        $loan->note = $request->note;
        $loan->save();

        if ($oldDate != $loan->due_at) {
            \Log::info(sprintf(
                'Endret forfallsdato for <a href="%s">utlånet</a> av %s fra %s til %s.',
                action('LoansController@getShow', $loan->id),
                $loan->item->thing->properties->get('name_indefinite.nob'),
                $oldDate->toDateString(),
                $loan->due_at->toDateString()
            ), ['library' => \Auth::user()->name]);
        }

        event(new LoanTableUpdated('update', $request, $loan));

        return redirect()->action('LoansController@getIndex')
            ->with('status', 'Lånet ble oppdatert');
    }

    /**
     * Mark the specified resource as lost.
     *
     * @param Loan $loan
     * @param Request $request
     * @return Response
     */
    public function lost(Loan $loan, Request $request)
    {
        $loan->lost();

        event(new LoanTableUpdated('lost', $request, $loan));

        return $this->loggedResponse([
            'status' => sprintf('Registrerte %s som tapt.', $loan->item->formattedLink(false, false)),
            'undoLink' => action('LoansController@restore', $loan->id),
        ]);
    }

    /**
     * Checkin the specified loan.
     *
     * @param AlmaClient $alma
     * @param CheckinRequest $request
     * @return Response
     */
    public function checkin(AlmaClient $alma, CheckinRequest $request)
    {
        if ($request->loan) {
            return $this->checkinLocalLoan($request->loan, $request, $alma);
        }

        if ($request->almaItem) {
            return $this->checkinAlmaItem($alma, $request->almaItem);
        }

        return response()->json([
            'status' => 'Ingenting har blitt returnert. Det kan argumenteres for at dette var en ' .
                'unødvendig operasjon, men hvem vet.'
        ]);
    }

    /**
     * Restores the specified loan.
     *
     * @param Loan $loan
     * @param Request $request
     * @return Response
     */
    public function restore(Loan $loan, Request $request)
    {
        if ($loan->is_lost) {
            $loan->found();
        } else {
            $loan->restore();
        }

        event(new LoanTableUpdated('restore', $request, $loan));

        return $this->loggedResponse([
            'status' => sprintf(
                '%s er fortsatt utlånt (<a href="%s">Detaljer</a>).',
                s($loan->item->thing->properties->get('name_definite.nob'))->upperCaseFirst(),
                action('LoansController@getShow', $loan->id)
            )
        ]);
    }

    /**
     * Checkout Alma item without creating a local copy.
     *
     * @param AlmaClient $client
     * @param AlmaItem $item
     * @param User $localUser
     * @param Request $request
     * @return Response
     */
    protected function checkoutAlmaItem(AlmaClient $client, AlmaItem $almaItem, User $localUser, Request $request)
    {
        $library = \Auth::user();

        if (empty($library->library_code)) {
            return $this->loggedResponse([
                'error' => 'Alma-utlån krever at bibliotekskode er registrert'
                    . ' i kontoinnstillingene.',
            ]);
        }

        if ($localUser->in_alma) {
            $almaUser = $client->users->get($localUser->alma_primary_id);
        } else {
            if (empty($library->temporary_barcode)) {
                return $this->loggedResponse([
                    'error' => 'Brukeren finnes ikke i Alma. Hvis du vil låne ut på midlertidig lånekort'
                        . ' må det registreres i kontoinnstillingene.',
                ]);
            }

            $almaUser = $client->users->get($library->temporary_barcode);
        }

        $almaLibrary = $client->libraries[$library->library_code];

        try {
            $response = $almaItem->checkOut($almaUser, $almaLibrary);
        } catch (RequestFailed $e) {
            return $this->loggedResponse([
                'error' => $e->getMessage(),
            ]);
        }

        if ($response->loan_status != 'ACTIVE') {
            return $this->loggedResponse([
                'error' => 'Utlån av Alma-dokument i Bibrex feilet, prøv i Alma i stedet.',
            ]);
        }

        # The Alma checkout was successful. If the user is not in Alma, we create a
        # temporary local item to keep track of the loan.
        if (!$localUser->in_alma) {
            $localItem = Item::withTrashed()->firstOrNew([
                'thing_id' => 1,
                'barcode' => $response->item_barcode,
            ]);
            $localItem->library_id = $library->id;
            $localItem->note = $response->title;
            $localItem->save();

            \Log::info(sprintf(
                'Opprettet midlertidig Bibrex-eksemplar for Alma-utlån (<a href="%s">Detaljer</a>)',
                action('ItemsController@show', $localItem->id)
            ), ['library' => \Auth::user()->name]);

            return $this->checkoutLocalItem($localItem, $localUser, $request);
        }

        # If the user exists in Alma, we don't create a local item.
        return $this->loggedResponse([
            'status' => sprintf(
                '%s (%s) ble lånt ut til %s i Alma. Forfaller: %s',
                $response->item_barcode,
                $response->title,
                $response->user_id,
                $response->due_date
            ),
        ]);
    }

    /**
     * Checkin Alma item.
     *
     * @param AlmaClient $client
     * @param AlmaItem $item
     * @return Response
     */
    protected function checkinAlmaItem(AlmaClient $client, AlmaItem $item)
    {
        $library = \Auth::user();

        if (empty($library->library_code)) {
            return $this->loggedResponse([
                'error' => 'Alma-innleveringer krever at bibliotekskode er registrert'
                    . ' i kontoinnstillingene.',
            ]);
        }

        $almaLibrary = $client->libraries[$library->library_code];

        $response = $item->scanIn($almaLibrary, 'DEFAULT_CIRC_DESK', [
            'place_on_hold_shelf' => 'true',
            'auto_print_slip' => 'true',
        ]);

        return $this->loggedResponse([
            'status' => sprintf(
                '<small>%s ble skanna inn i Alma, og Alma svarte:</small><br>%s',
                $item->item_data->barcode,
                $response->getMessage()
            ),
        ]);
    }

    /**
     * @param Loan $loan
     * @param Request $request
     * @param AlmaClient $alma
     * @return Response
     */
    protected function checkinLocalLoan(Loan $loan, Request $request, AlmaClient $alma)
    {
        $library = \Auth::user();

        if ($loan->is_lost) {
            $loan->found();
            return $this->loggedResponse(['status' => sprintf(
                '%s var registrert som tapt, men er nå tilbake!',
                $loan->item->formattedLink(true)
            )]);
        }

        if ($loan->trashed()) {
            if ($loan->item->thing_id == 1 && !empty($library->library_code)) {
                // Could still be on loan in Alma
                $almaItem = $alma->items->fromBarcode($loan->item->barcode);
                return $this->checkinAlmaItem($alma, $almaItem);
            }

            return response()->json(['status' => sprintf(
                '%s var allerede levert (men det går greit).',
                $loan->item->formattedLink(true)
            )], 200);
        }

        $loan->checkIn();

        $user = $loan->user;
        $user->last_loan_at = Carbon::now();
        $user->save();

        event(new LoanTableUpdated('checkin', $request, $loan));

        if ($loan->item->thing_id == 1 && !empty($library->library_code)) {
            $almaItem = $alma->items->fromBarcode($loan->item->barcode);
            return $this->checkinAlmaItem($alma, $almaItem);
        }

        return $this->loggedResponse([
            'status' => sprintf(
                'Returnerte %s (<a href="%s">Detaljer</a>).',
                $loan->item->thing->properties->get('name_indefinite.nob'),
                action('LoansController@getShow', $loan->id)
            ),
            'undoLink' => action('LoansController@restore', $loan->id),
        ]);
    }
}
