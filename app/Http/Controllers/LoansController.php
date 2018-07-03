<?php

namespace App\Http\Controllers;

use App\Events\LoanTableUpdated;
use App\Http\Requests\CheckoutRequest;
use App\Item;
use App\Loan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class LoansController extends Controller
{
    /**
     * Validation error messages.
     *
     * @static array
     */
    protected $messages = [
        'user.required' => 'Trenger enten navn eller låne-ID.',
        'user.id.required_without' => 'Trenger enten navn eller låne-ID.',
        'user.name.required_without' => 'Trenger enten navn eller låne-ID.',
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
		$library = \Auth::user();

		$r = response()->view('loans.index', array(
            'has_things' => !is_null($library->things()->first()),
			'loan_ids' => \Session::get('loan_ids', array()),
            'tab' => \Session::get('tab', 'default'),
		));

		$r->header('Cache-Control', 'private, no-store, no-cache, must-revalidate, max-age=0');
		return $r;
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

        $loans = Loan::with('item.thing','user','notifications')
            ->where('library_id', $library->id)
            ->orderBy('created_at','desc')->get();

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
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    // public function messages()
    // {
    //     return [
    //         // 'user.startswithuo' => 'Kun kortnumre som starter på «uo» (vanlige studentkort) blir importert automatisk. Kortnumre som starter på «ubo» (ansattkort og nøkkelkort) må du registrere manuelt med LTREG i Bibsys. For kort fra andre institusjoner kan du bruke F12 LTKOP når du er på LTREG-skjermen.',
    //         // 'dokid.required' => 'Dokid må fylles ut.',
    //         // 'dokid.regex' => 'Dokid er ikke et dokid.',
    //         // 'thing.exists' => 'Tingen finnes ikke',

    //         // 'user.required' => 'Trenger enten navn eller låne-ID.',
    //         // 'user.id' => ['required_without' => 'Trenger enten navn eller låne-ID.'],
    //         // 'user.name' => ['required_without' => 'Trenger enten navn eller låne-ID.'],
    //     ];
    //}

    /**
     * Store a newly created resource in storage.
     *
     * @param CheckoutRequest $request
     * @return Response
     */
	public function checkout(CheckoutRequest $request)
	{
		// Create new loan
		$loan_ids = array();

		$loan = new Loan();
		$loan->user_id = $request->user->id;
		$loan->item_id = $request->item->id;
		$loan->due_at = Carbon::now()
            ->addDays($request->item->thing->loan_time)
            ->setTime(0, 0, 0);
        $loan->as_guest = false;
		if (!$loan->save()) {
		    return response()->json(['errors' => $loan->errors], 409);
		}
		$loan_ids[] = $loan->id;

        $request->user->loan_count += 1;
        $request->user->last_loan_at = Carbon::now();
        $request->user->save();

        \Log::info(sprintf('Lånte ut %s (<a href="%s">Detaljer</a>).',
            $request->item->thing->properties->name_indefinite->nob,
            action('LoansController@getShow', $loan->id)
        ));
        event(new LoanTableUpdated($loan_ids));

		if ($request->localUser) {
		    return response()->json([
		        'status' => 'Utlånet ble registrert. VIKTIG: Siden dette er en ny låner må du registrere litt informasjon om vedkommende.',
                'user' => action('UsersController@getEdit', $request->user->id),
                'loan_ids' => $loan_ids,
            ]);

		} else {
            return response()->json([
                'status' => 'Utlånet ble registrert.',
                'loan_ids' => $loan_ids,
            ]);
        }
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

        $old_date = $loan->due_at;

		$loan->due_at = Carbon::parse($request->due_at);
		$loan->note = $request->note;
		$loan->save();

        \Log::info(sprintf('Endret forfallsdato for <a href="%s">utlånet</a> av %s fra %s til %s.',
            action('LoansController@getShow', $loan->id),
            $loan->item->thing->properties->name_indefinite->nob,
            $old_date->toDateString(),
            $loan->due_at->toDateString()
        ));
        event(new LoanTableUpdated([$loan->id]));

		return redirect()->action('LoansController@getShow', $loan->id)
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
		\Log::info('Registrerte ' . $loan->item->thing->properties->name_indefinite->nob . ' som tapt' .
            ' (<a href="'. action('LoansController@getShow', $loan->id) . '">Detaljer</a>)');

        $loan->lost();

        event(new LoanTableUpdated([]));

        return response()->json([
            'status' => sprintf('%s ble registrert som tapt.', $loan->item->formattedLink(true)),
            'undoLink' => action('LoansController@getRestore', $loan->id),
        ]);
    }

    /**
     * Checkin the specified loan.
     *
     * @param Request $request
     * @return Response
     */
    public function checkin(Request $request)
    {
        $status = null;
        $undoLink = null;
        if ($request->input('barcode')) {
            $loan = Loan::with(['item', 'item.thing', 'user'])
                ->whereHas('item', function ($query) use ($request) {
                    $query->where('dokid', '=', $request->input('barcode'));
                })
                ->first();
        } else if ($request->input('loan')) {
            $loan = Loan::with(['item', 'item.thing', 'user'])
                ->find($request->input('loan'));
        } else {
            return response()->json([
                'status' => 'Ingenting ble returnert.',
            ], 200);
        }

        if (is_null($loan)) {
            $loan = Loan::with(['item', 'item.thing', 'user'])
                ->withTrashed()
                ->whereHas('item', function ($query) use ($request) {
                    $query->where('dokid', '=', $request->input('barcode'));
                })
                ->orderBy('updated_at', 'desc')
                ->first();

        }

        if (is_null($loan)) {
            $item = Item::withTrashed()->where('dokid', '=', $request->input('barcode'))->first();
            if ($item) {
                return response()->json([
                    'error' => sprintf(
                        'Denne %s var ikke utlånt.',
                        $item->formattedLink(false)
                    )
                ], 422);
            }
            return response()->json([
                'error' => 'Bibrex kjente ikke igjen strekkoden «' . $request->input('barcode') . '».',
            ], 422);
        }

        if ($loan->is_lost) {
            $status = sprintf(
                'Denne %s var registrert som tapt, men ikke nå lenger (takket være deg)!',
                $loan->item->formattedLink(false)
            );
            $loan->found();

        } else if ($loan->item->trashed()) {
            $status = sprintf(
                'Du store min hatt, denne %s har faktisk blitt kassert i mellomtiden!',
                $loan->item->formattedLink(false)
            );

        } elseif ($loan->trashed()) {
            $status = sprintf(
                'Denne %s var strengt tatt ikke utlånt (men det går helt greit).',
                $loan->item->formattedLink(false)
            );

        } else {
            $status = sprintf('%s ble returnert.', $loan->item->formattedLink(true));
            $undoLink = action('LoansController@getRestore', $loan->id);
        }

        \Log::info(sprintf('Returnerte %s (<a href="%s">Detaljer</a>).',
            $loan->item->thing->properties->name_definite->nob,
            action('LoansController@getShow', $loan->id)
        ));

        $user = $loan->user;

        $loan->checkIn();

        $user->last_loan_at = Carbon::now();
        $user->save();

        event(new LoanTableUpdated());

        return response()->json([
            'status' => $status,
            'undoLink' => $undoLink,
        ]);
    }

    /**
     * Restores the specified loan.
     *
     * @param Loan $loan
     * @return Response
     */
	public function getRestore(Loan $loan)
	{
        \Log::info(sprintf('Angret retur av %s (<a href="%s">Detaljer</a>).',
            $loan->item->thing->properties->name_indefinite->nob,
            action('LoansController@getShow', $loan->id)
        ));

        if ($loan->is_lost) {
            $loan->found();
        } else {
            $loan->restore();
        }

        event(new LoanTableUpdated([$loan->id]));

        return response()->json([
            'status' => sprintf('Angret. %s er fortsatt utlånt til %s.',
                $loan->item->formattedLink(true),
                $loan->user->name),
        ]);
	}

}
