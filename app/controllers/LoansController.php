<?php

class LoansController extends BaseController {

	private $rules = array(
		'ltid' => array('required'),
		'dokid' => array('regex:/^[0-9a-zA-Z]{9}$/')
	);

	private $messages = array(
		'ltid.required' => 'ltid må fylles ut',
		'ltid.regex' => 'ltid er ikke et ltid',
		'dokid.required' => 'dokid må fylles ut',
		'dokid.regex' => 'dokid er ikke et dokid'
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

		return Response::view('loans.index', array(
			'loans' => $loans,
			'things' => $things,
			'loan_ids' => Session::get('loan_ids', array())
		));
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function getShow($id)
	{
		$loan = Loan::find($id);
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

		// Check if Document exists, create if not
		$thing = Thing::where('id','=',Input::get('thing'))->first();
		if (!$thing) {
			return Redirect::action('LoansController@getIndex')
				->with('status', 'Tingen finnes ikke!');
		}

		if ($thing->id == 1) {

			// Hent DOKID fra DOKID/KNYTTID
			$unknown_id = Input::get('dokid');
			$curl = New Curl;
			$ids = $curl->simple_get('http://linode.biblionaut.net/services/getids.php?id=' . $unknown_id);
			$ids = json_decode($ids);

			if (empty($ids->dokid)) {
				return Redirect::action('LoansController@getIndex')
					->with('status', 'Dokumentet finnes ikke');
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
				return Redirect::action('LoansController@getIndex')
					->with('status', 'Dokumentet er allerede utlånt');
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
		if (preg_match('/^[0-9a-zA-Z]{10}$/', $user_input)) {
			$ltid = $user_input;
			$user = User::where('ltid','=',$user_input)->first();
		} else {
			$user_id = Input::get('user_id');
			if (!empty($user_id)) {
				$user = User::find($user_id);
			} else {
				if (strpos($user_input, ',') === false) {
					return Redirect::action('LoansController@getIndex')
						->with('status', 'Navnet må skrives på formen "Etternavn, Fornavn".');
				} else {
					$name = explode(',', $user_input);
					$name = aray_map('trim', $name);
					$user = User::where('lastname','=',$name[0])->where('firstname','=',$name[1])->first();
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
			$user->save();
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
			$loan = new Loan();
			$loan->user_id = $user->id;
			$loan->document_id = $dok->id;
			$loan->save();
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
		$ncip = new Ncip();
		foreach (Loan::with('document','user')->get() as $loan) {
			if ($loan->document->thing_id == 1) {
				$dokid = $loan->document->dokid;
				echo "Sjekker: " . $loan->representation() . " : ";
				if ($loan->user->in_bibsys) {
					$nr = $loan->user->ltid;
				} else {
					$nr = $loan->guestNumber;
				}
				echo " $nr : ";
				if (!isset($user_loans[$nr])) {
					$response = $ncip->lookupUser($nr);
					$user_loans[$nr] = array();
					foreach ($response['loanedItems'] as $item) {
						$user_loans[$nr][] = $item['id'];
					}
				}
				if (in_array($dokid, $user_loans[$nr])) {
					echo " fortsatt utlånt";
				} else {
					echo " returnert i BIBSYS";
					$loan->delete();
				}
				echo "\n";
			}
		}
		exit();
	}

}