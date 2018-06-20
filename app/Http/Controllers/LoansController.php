<?php

namespace App\Http\Controllers;

use App\Alma\User as AlmaUser;
use App\Item;
use App\Loan;
use App\Rules\StartsWithUo;
use App\Rules\ThingExists;
use App\Thing;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Scriptotek\Alma\Client as AlmaClient;

class LoansController extends Controller
{
    /**
     * Validation error messages.
     *
     * @static array
     */
    protected $messages = array(
        'user.required' => 'Mangler låntaker.',
        'thing.required' => 'Uten ting blir det bare ingenting.',
    );

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
	 * @return Response
	 */
	public function getIndex()
	{
		$library = \Auth::user();
		$things = $library->things()->orderBy('name')->get();

		// A list of all loans for the current library
		$loans = Loan::with('item.thing','user')
			->where('library_id', $library->id)
			->orderBy('created_at','desc')->get();

		$r = response()->view('loans.index', array(
			'loans' => $loans,
			'things' => $things,
			'loan_ids' => \Session::get('loan_ids', array())
		));
		$r->header('Cache-Control', 'private, no-store, no-cache, must-revalidate, max-age=0');
		return $r;
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
    public function messages()
    {
        return [
            'user.startswithuo' => 'Kun kortnumre som starter på «uo» (vanlige studentkort) blir importert automatisk. Kortnumre som starter på «ubo» (ansattkort og nøkkelkort) må du registrere manuelt med LTREG i Bibsys. For kort fra andre institusjoner kan du bruke F12 LTKOP når du er på LTREG-skjermen.',
            'user.required' => 'Trenger enten navn eller låne-ID.',
            'dokid.required' => 'Dokid må fylles ut.',
            'dokid.regex' => 'Dokid er ikke et dokid.',
            'count.integer' => 'Antall må være et heltall.',
            'count.between' => 'Antall må være et tall mellom 1 og 10.',
            'thing.exists' => 'Tingen finnes ikke',
        ];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param AlmaClient $alma
     * @param Request $request
     * @return Response
     * @throws ValidationException
     */
	public function postStore(AlmaClient $alma, Request $request)
	{
        $request->validate([
            'user' => ['required', 'string', new StartsWithUo()],
            'thing' => [new ThingExists()],
            'count' => ['integer', 'between:1,10'],
            // 'dokid' => ['regex:/^[0-9a-zA-Z]{9}$/'],
        ], $this->messages);

        $lib = \Auth::user();

        // ======================== Lookup item and thing ========================

        $item = Item::where('dokid', '=', $request->thing)->first();
        if (!is_null($item)) {
            $thing = $item->thing;

            // Check if already on loan
            $loan = $item->loans()->first();
            if ($loan) {
                throw ValidationException::withMessages([
                    'already_on_loan' => ['Tingen er allerede utlånt.'],
                ]);
            }
        } else {
            // If not a barcode, then maybe a thing name
            $thing = Thing::where('name', '=', $request->thing)->first();

            // Then find a generic item, if one exists
            if (array_get($thing->library_settings, 'require_item')) {
                throw ValidationException::withMessages([
                    'needs_barcode' => ['Utlån av denne tingen må gjøres med strekkode.'],
                ]);
            }
            $item = Item::where('thing_id','=',$thing->id)->first();
            if (!$item) {
                $item = new Item();
                $item->thing_id = $thing->id;
                $item->save();
            } elseif (!is_null($item->dokid)) {
                throw ValidationException::withMessages([
                    'needs_barcode' => ['Utlån av denne tingen må gjøres med strekkode.'],
                ]);
            }
        }

		// if ($thing->id == 1) {

		// 	// Hent DOKID fra DOKID/KNYTTID
		// 	$unknown_id = $request->input('dokid');

		// 	if (empty($unknown_id)) {
  //               throw ValidationException::withMessages([
  //                   'dokid_empty' => ['Intet dokument oppgitt.'],
  //               ]);
		// 	}
  //           $curl = \App::make('Curl');
  //           touch(storage_path('cookie_file'));
  //           $curl->cookie_file = storage_path('cookie_file');
  //           $curl->follow_redirects = false;
		// 	$ids = $curl->get('http://services.biblionaut.net/getids.php?id=' . $unknown_id);
		// 	$ids = json_decode($ids);

		// 	if (empty($ids->dokid)) {
  //               throw ValidationException::withMessages([
  //                   'document_not_found' => ['Dokumentet finnes ikke.'],
  //               ]);
		// 	}

		// 	// Sjekk om dokumentet finnes lokalt
		// 	$dok = Document::where('dokid','=',$ids->dokid)->first();
		// 	if (!$dok) {
		// 		$dok = new Document();
		// 		$dok->thing_id = $thing->id;
		// 		$dok->dokid = $ids->dokid;
		// 		$dok->objektid = $ids->objektid;
		// 		if ($unknown_id != $ids->dokid) {
		// 			$dok->knyttid = $unknown_id;
		// 		}
		// 		$dok->save();
		// 	}

		// 	// Check if already on loan
		// 	$loan = $dok->loans()->first();
		// 	if ($loan) {
  //               throw ValidationException::withMessages([
  //                   'already_on_loan' => ['Dokumentet er allerede utlånt.'],
  //               ]);
		// 	}
		// }

        // ======================== Lookup or import user ========================

        $user_input = $request->input('user_id') ?: $request->input('user');
        $newTempUser = false;

        if (strpos($user_input, ',') !== false) {
            $name = explode(',', $user_input);
            $name = array_map('trim', $name);
            $user = User::where('lastname','=',$name[0])->where('firstname','=',$name[1])->first();
        } else {
            $user = User::where('id','=',$user_input)->orWhere('barcode','=',$user_input)->orWhere('university_id','=',$user_input)->first();
        }

        if (is_null($user)) {
	        \Log::info('Ingen lokal bruker funnet for: "' . $user_input . '"');

            // Try lookup by primary id first. Since Alma allows the primary id
            // to be *anything*, it can overlap with names, etc.
            $query = 'primary_id~' . $user_input;
            $users = collect($alma->users->search($query, ['limit' => 5]))->map(function($u) {
                return new AlmaUser($u);
            });
            if (count($users) == 0) {
                $query = 'ALL~' . $user_input;
                $users = collect($alma->users->search($query, ['limit' => 5]))->map(function($u) {
                    return new AlmaUser($u);
                });
            }

            if (count($users) > 1) {
                \Log::warning('Mer enn én bruker ble funnet i Alma for "' . $user_input . '".');
                throw ValidationException::withMessages([
                    'user' => ['Mer enn én bruker ble funnet i Alma.'],
                ]);
            } elseif (count($users) == 1) {
            	$barcode = $users[0]->getBarcode();
            	$univId = $users[0]->getUniversityId();
            	$user = User::where('barcode', '=', $barcode)->orWhere('university_id', '=', $univId)->first();
            	if (is_null($user)) {
            		$user = new User();
            	}
                $user->mergeFromUserResponse($users[0]);
                $user->save();
                \Log::info('Importerte bruker fra Alma: "' . $users[0]->id. '"');
            } else {
                if (strpos($user_input, ',') !== false) {

                    if (!array_get($lib->options, 'guestcard_for_cardless_loans', false)) {
                        throw ValidationException::withMessages([
                            'user' => ['Brukeren ble ikke funnet i Alma og opprettelse av lokale brukere er ikke aktivert for dette biblioteket. Det kan aktiveres i <a href="' . action('LibrariesController@getMyAccount') . '">kontoinnstillingene</a>.'],
                        ]);
                    }

                    $name = explode(',', $user_input);
                    $name = array_map('trim', $name);
                    $user = User::create(['firstname' => $name[0], 'lastname' => $name[1]]);
                    \Log::info('Oppretter ny midlertidig bruker: ' . $user->lastname . ', ' . $user->firstname);
                    $newTempUser = true;
                } else {
                    \Log::info('Ikke funnet i Alma: ' . $user_input);
                    throw ValidationException::withMessages([
                        'user' => ['Brukeren ble ikke funnet.'],
                    ]);
                }
            }
        }

		// if ($this->isLTID($user_input)) {
		// 	$ltid = $user_input;
		// 	$user = User::where('ltid','=',$user_input)->first();
		// } else if (preg_match('/[0-9]/', $user_input)) {
  //           throw ValidationException::withMessages([
  //               'invalid_ltid_format' => ['Kortnummeret har feil lengde.'],
  //           ]);
		// } else {
		// 	$user_id = $request->input('ltid_id');

		// 	if (!empty($user_id)) {
		// 		$user = User::find($user_id);
		// 	} else {
		// 		if (strpos($user_input, ',') === false) {
  //                   throw ValidationException::withMessages([
  //                       'invalid_name_format' => ['Navnet må skrives på formen "Etternavn, Fornavn".'],
  //                   ]);

		// 		} else {

		// 			$name = explode(',', $user_input);
		// 			$name = array_map('trim', $name);
		// 			$user = User::where('lastname','=',$name[0])
		// 				->where('firstname','=',$name[1])->first();

		// 		}
		// 	}
		// }

        // ======================== Checkout ========================

		if ($thing->id == 1) {
			$count = 1;
		} else {
			$count = $request->input('count', 1);
		}
		$count = intval($count);

		// Create new loan(s)
		$loan_ids = array();
		for ($i=0; $i < $count; $i++) {
			$loan = new Loan();
			$loan->user_id = $user->id;
			$loan->item_id = $item->id;
			$loan->due_at = Carbon::now()->addDays($thing->loan_time)->setTime(0, 0, 0);
            $loan->as_guest = false;
			if (!$loan->save()) {
				return redirect()->action('LoansController@getIndex')
					->withErrors($loan->errors)
					->withInput();
			}
			$loan_ids[] = $loan->id;

            $user->loan_count += 1;
            $user->last_loan_at = Carbon::now();
            $user->save();
		}

		\Log::info('Lånte ut <a href="'. action('LoansController@getShow', $loan_ids[0]) . '">' . $thing->name . '</a>.');


		if ($newTempUser) {
			return redirect()->action('UsersController@getEdit', $user->id)
				->with('status', 'Utlånet er registrert. VIKTIG: Siden dette er en ny låner må du registrere litt informasjon om vedkommende.');
		} else {
			return redirect()->action('LoansController@getIndex')
				->with('status', ($count == 1 ? 'Utlånet' : 'Utlånene') . ' er registrert.')
				->with('loan_ids', $loan_ids);
		}

	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function getEdit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function postUpdate($id)
	{
		//
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param Loan $loan
     * @param Request $request
     * @return Response
     */
	public function getLost(Loan $loan, Request $request)
	{
		\Log::info('<a href="'. action('LoansController@getShow', $loan->id) . '">Utlån</a> av ' . $loan->item->thing->name . ' ble registrert som tapt.');

		$repr = $loan->representation();
		$itemId = $loan->item->id;
		$user = $loan->user->getName();

		$loan->is_lost = true;
		$loan->save();

		$loan->checkIn();

		if ($loan->item->dokid) {
			$loan->item->delete();
		}

		$returnTo = $request->input('returnTo', 'items.show');

		switch ($returnTo) {
			case 'loans.index':
				$redir = redirect()->action('LoansController@getIndex');
				break;
			default:
				$redir = redirect()->action('ItemsController@show', $itemId);
		}
		return $redir->with('status', $repr .' ble registrert som rotet bort. <a href="' . action('LoansController@getRestore', $loan->id) . '" class="alert-link">Angre</a>');
	}

    /**
     * Remove the specified resource from storage.
     *
     * @param Loan $loan
     * @param Request $request
     * @return Response
     */
	public function getDestroy(Loan $loan, Request $request)
	{
		\Log::info('Returnerte <a href="'. action('LoansController@getShow', $loan->id) . '">' . $loan->item->thing->name . '</a>.');

		$user = $loan->user;

		$repr = $loan->representation();
		$itemId = $loan->item->id;
		$loan->checkIn();

        $user->last_loan_at = Carbon::now();
        $user->save();

		$returnTo = $request->input('returnTo', 'items.show');

		switch ($returnTo) {
			case 'loans.index':
				$redir = redirect()->action('LoansController@getIndex');
				break;
			default:
				$redir = redirect()->action('ItemsController@show', $itemId);
		}
		return $redir->with('status', $repr .' ble levert inn for ' . $user->getName() . '. <a href="' . action('LoansController@getRestore', $loan->id) . '" class="alert-link">Angre</a>');
	}

    /**
     * Restores the specified resource into storage.
     *
     * @param Loan $loan
     * @return Response
     */
	public function getRestore(Loan $loan)
	{
		\Log::info('Returen av <a href="'. action('LoansController@getShow', $loan->id) . '">utlånet</a> av ' . $loan->item->thing->name . ' ble angret.');

		$loan->restore();

		$loan->is_lost = false;
		$loan->save();

		return redirect()->action('LoansController@getIndex')
			->with('status', 'Innleveringen ble angret.');
	}

}
