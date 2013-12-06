<?php

class LoansController extends BaseController {

	private $rules = array(
		'ltid' => 'required|startswithuo', // Husk: custom validators i app/start/global.php
		'dokid' => 'regex:/^[0-9a-zA-Z]{9}$/',
		'count' => 'integer|between:1,10'
	);

	private $messages = array(
		'ltid.startswithuo' => 'Kun kortnumre som starter på «uo» (vanlige studentkort) blir importert automatisk. Kortnumre som starter på «ubo» (ansattkort og nøkkelkort) må du registrere manuelt med LTREG i Bibsys. For kort fra andre institusjoner kan du bruke F12 LTKOP når du er på LTREG-skjermen.',
		'ltid.required' => 'Trenger enten navn eller LTID.',
		'dokid.required' => 'Dokid må fylles ut.',
		'dokid.regex' => 'Dokid er ikke et dokid.',
		'count.integer' => 'Antall må være et heltall.',
		'count.between' => 'Antall må være et tall mellom 1 og 10.'
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
		$library_id = Auth::user()->id;

		// A list of all loans for the current library
		$loans = Loan::with('document.thing','user')
			->where('library_id', $library_id)
			->orderBy('created_at','desc')->get();

		// A list of all things for the select box
		$things = array();
		$q = Thing::where('disabled', false)
			->where('library_id', $library_id)
			->orWhere('library_id', NULL);
		foreach ($q->get() as $thing) {
			$things[$thing->id] = $thing->name;
		}

		$r = Response::view('loans.index', array(
			'loans' => $loans,
			'things' => $things,
			'loan_ids' => Session::get('loan_ids', array())
		));
		$r->header('Cache-Control', 'private, no-store, no-cache, must-revalidate, max-age=0');
		return $r;
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function getShow($id)
	{
		$loan = Loan::withTrashed()->find($id);
		if ($loan) {
			return Response::view('loans.show', array('loan' => $loan));
		} else {
			return Response::view('errors.missing', array('what' => 'Lånet'), 404);
		}
	}

	/**
	 * TODO: Move method to a more reasonable place (where is that?)
	 */
	public function isLTID($value)
	{
		return (											// If
			preg_match('/^[0-9a-zA-Z]{10}$/', $value) &&    // ... it's 10 characters long
			preg_match('/[0-9]{6,}/', $value)				// ... and contains at least six adjacent numbers
		);													// ... we assume it's a LTID :)
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function postStore()
	{
		$validator = Validator::make(Input::all(), $this->rules, $this->messages);

		if ($validator->fails())
		{
			return Redirect::action('LoansController@getIndex')
				->withErrors($validator)
				->withInput();
		}

		// Must be called after $validator->fails() ?
		$messagebag = $validator->getMessageBag();

		// Check if Document exists, create if not
		$thing = Thing::where('id','=',Input::get('thing'))->first();
		if (!$thing) {
			$messagebag->add('thing_not_found', 'Tingen finnes ikke');
			return Redirect::action('LoansController@getIndex')
				->withErrors($validator)
				->withInput();
		}

		if ($thing->id == 1) {

			// Hent DOKID fra DOKID/KNYTTID
			$unknown_id = Input::get('dokid');

			if (empty($unknown_id)) {
				$messagebag->add('dokid_empty', 'Intet dokument oppgitt');
				return Redirect::action('LoansController@getIndex')
					->withErrors($validator)
					->withInput();
			}
			$curl = App::make('Curl');
			$ids = $curl->get('http://services.biblionaut.net/getids.php?id=' . $unknown_id);
			$ids = json_decode($ids);

			if (empty($ids->dokid)) {
				$messagebag->add('document_not_found', 'Dokumentet finnes ikke');
				return Redirect::action('LoansController@getIndex')
					->withErrors($validator);
			}

			// Sjekk om dokumentet finnes lokalt
			$dok = Document::where('dokid','=',$ids->dokid)->first();
			if (!$dok) {
				$dok = new Document();
				$dok->thing_id = $thing->id;
				$dok->dokid = $ids->dokid;
				$dok->objektid = $ids->objektid;
				if ($unknown_id != $ids->dokid) {
					$dok->knyttid = $unknown_id;
				}
				$dok->save();
			}

			// Check if already on loan
			$loan = $dok->loans()->first();
			if ($loan) {
				$messagebag->add('already_on_loan', 'Dokumentet er allerede utlånt');
				return Redirect::action('LoansController@getIndex')
					->withErrors($validator);
			}

		} else {

			$dok = Document::where('thing_id','=',$thing->id)->first();
			if (!$dok) {
				$dok = new Document();
				$dok->thing_id = $thing->id;
				$dok->save();
			}

		}

		// Check if User exists, create if not
		$user_input = Input::get('ltid');
		$ltid = false;
		$user = false;
		$name = false;
		if ($this->isLTID($user_input)) {
			$ltid = $user_input;
			$user = User::where('ltid','=',$user_input)->first();
		} else if (preg_match('/[0-9]/', $user_input)) {
			$messagebag->add('invalid_ltid_format', 'Kortnummeret har feil lengde.');
			return Redirect::action('LoansController@getIndex')
				->withErrors($validator)
				->withInput();
		} else {
			$user_id = Input::get('user_id');
			if (!empty($user_id)) {
				$user = User::find($user_id);
			} else {
				if (strpos($user_input, ',') === false) {

					$messagebag->add('invalid_name_format', 'Navnet må skrives på formen "Etternavn, Fornavn".');
					return Redirect::action('LoansController@getIndex')
						->withErrors($validator)
						->withInput();

				} else {

					$name = explode(',', $user_input);
					$name = array_map('trim', $name);
					$user = User::where('lastname','=',$name[0])
						->where('firstname','=',$name[1])->first();

				}
			}
		}

		$lib = Auth::user();
		$new_user = false;
		if (!$user) {
			$new_user = true;
			$user = new User();
			if ($ltid === false) {
				if (!array_get($lib->options, 'guestcard_for_cardless_loans', false)) {
					$messagebag->add('cardless_loans_not_activated', 'Kortløse utlån er ikke aktivert for dette biblioteket. Det kan aktiveres i <a href="' . action('LibrariesController@myAccount') . '">kontoinnstillingene</a>.');
					return Redirect::action('LoansController@getIndex')
						->withErrors($validator)
						->withInput();
				}
			} else {
				$user->ltid = $ltid;
			}
			if ($name !== false) {
				$user->lastname = $name[0];
				$user->firstname = $name[1];
			}
			if (!$user->save()) {
				return Redirect::action('LoansController@getIndex')
					->withErrors($user->errors)
					->withInput();
			}
		}

		if ($thing->id == 1) {
			$count = 1;
		} else {
			$count = Input::get('count', 1);
		}
		$count = intval($count);

		// Create new loan(s)
		$loan_ids = array();
		for ($i=0; $i < $count; $i++) {
			$loan = new Loan;
			$loan->user_id = $user->id;
			$loan->document_id = $dok->id;
			if (!$loan->save()) {
				return Redirect::action('LoansController@getIndex')
					->withErrors($loan->errors)
					->withInput();
			}
			$loan_ids[] = $loan->id;
		}

		if ($new_user) {
			if ($user->in_bibsys) {
				return Redirect::action('UsersController@getEdit', $user->id)
					->with('status', 'Utlånet er lagret. Siden dette er en ny BIBREX-låner må du kontrollere og lagre opplysningene importert fra BIBSYS.');
			} else {
				return Redirect::action('UsersController@getEdit', $user->id)
					->with('status', 'Utlånet er lagret. Siden dette er en ny låner må du registrere litt informasjon om vedkommende.');
			}
		} else {
			return Redirect::action('LoansController@getIndex')
				->with('status', ($count == 1 ? 'Utlånet' : 'Utlånene') . ' er lagret.')
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
	 * @param  int  $id
	 * @return Response
	 */
	public function getDestroy($id)
	{
		$loan = Loan::find($id);
		if (!$loan) {
			// App::abort(404);
		    return Response::view('errors.missing', array('what' => 'Lånet'), 404);
		}
		$repr = $loan->representation();
		$docid = $loan->document->id;
		$user = $loan->user->name();
		$loan->checkIn();

		$returnTo = Input::get('returnTo', 'documents.show');

		switch ($returnTo) {
			case 'loans.index':
				$redir = Redirect::action('LoansController@getIndex');
				break;
			default:
				$redir = Redirect::action('DocumentsController@getShow', $docid);
		}
		return $redir->with('status', $repr .' ble levert inn for ' . $user . '. <a href="' . URL::action('LoansController@getRestore', $id) . '" class="alert-link">Angre</a>');	    	
	}

	/**
	 * Restores the specified resource into storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function getRestore($id)
	{
		$loan = Loan::withTrashed()->find($id);
		if (!$loan) {
			// App::abort(404);
		    return Response::view('errors.missing', array('what' => 'Lånet'), 404);
		}
		$docid = $loan->document->id;
		$loan->restore();
		return Redirect::action('DocumentsController@getShow', $docid)
			->with('status', 'Innleveringen ble angret.');
	}

}
