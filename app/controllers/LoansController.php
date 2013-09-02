<?php

class LoansController extends BaseController {

	private $rules = array(
		'ltid' => array('required'),
		'dokid' => array('regex:/^[0-9a-zA-Z]{9}$/'),
		'count' => array('integer', 'between:1,10')
	);

	private $messages = array(
		'ltid.required' => 'Trenger enten navn eller LTID.',
		'dokid.required' => 'Dokid må fylles ut.',
		'dokid.regex' => 'Dokid er ikke et dokid.',
		'count.integer' => 'Antall må være et heltall.',
		'count.between' => 'Antall må være et tall mellom 1 og 10.'
	);

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getIndex()
	{
		$loans = Loan::with('document.thing','user')->orderBy('created_at','desc')->get();
		$things = array();
		foreach (Thing::all() as $thing) {
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
			$ids = $curl->simple_get('http://linode.biblionaut.net/services/getids.php?id=' . $unknown_id);
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
		if (preg_match('/^[0-9a-zA-Z]{10}$/', $user_input) && preg_match('/[0-9]/', $user_input)) {
			$ltid = $user_input;
			$user = User::where('ltid','=',$user_input)->first();
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

		$new_user = false;
		if (!$user) {
			$new_user = true;
			$user = new User();
			if ($ltid !== false) $user->ltid = $ltid;
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
					->with('status', 'Dokumentet ble lånt ut. Brukeren ble funnet i BIBSYS.');				
			} else {
				return Redirect::action('UsersController@getEdit', $user->id)
					->with('status', 'Dokumentet ble lånt ut. Siden dette er en ny låner må du registrere litt informasjon om vedkommende.');				
			}
		} else {
			return Redirect::action('LoansController@getIndex')
				->with('status', ($count == 1 ? 'Dokumentet' : 'Dokumentene') . ' ble lånt ut')
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
		$loan->delete();

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

	/**
	 * Checks if loans has been returned in BIBSYS
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function getSync()
	{
		$user_loans = array();
		$due = array();
		header("Content-type: text/html; charset=utf-8");
		echo "Starter sync...<br />\n";
		$ncip = new NcipClient();
		$guest_ltid = Config::get('app.guest_ltid');

		foreach (Loan::with('document','user')->get() as $loan) {
			if ($loan->document->thing_id == 1) {
				$dokid = $loan->document->dokid;
				echo "Sjekker: " . $loan->representation() . " : ";
				$ltid = $loan->as_guest ? $guest_ltid : $loan->user->ltid;
				//$loan->as_guest = !$loan->user->in_bibsys;

				echo " $ltid ";
				if (!isset($user_loans[$ltid])) {
					$response = $ncip->lookupUser($ltid);
					$user_loans[$ltid] = array();
					foreach ($response->loanedItems as $item) {
						$user_loans[$ltid][] = $item['id'];
						$due[$item['id']] = $item['dateDue'];
					}
				}
				echo " (" . count($user_loans[$ltid]) . " lån), ";
				if (in_array($dokid, $user_loans[$ltid])) {
					echo " fortsatt utlånt";
					$loan->due_at = $due[$dokid];
				} else {
					echo " returnert i BIBSYS";
					//$loan->delete();
				}
				echo "<br />\n";
				$loan->save();
			}
		}
		exit();
	}

}
