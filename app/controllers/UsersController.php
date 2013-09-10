<?php

class UsersController extends BaseController {

	private $rules = array(
		'ltid' => array('regex:/^[0-9a-zA-Z]{10}$/'),
		'lastname' => array('required'),
		'firstname' => array('required'),
		'lang' => array('required')
	);

	private $messages = array(
		'ltid.regex' => 'ltid er ikke et ltid',
		'lastname.required' => 'etternavn må fylles ut',
		'firstname.required' => 'fornavn må fylles ut',
		'lang.required' => 'språk må fylles ut'
	);
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getIndex()
	{
		$users = array();
		foreach (User::with('loans')->get() as $user) {
			$users[] = array(
				'id' => $user->id,
				'value' => $user->lastname . ', ' . $user->firstname,
				'lastname' => $user->lastname,
				'firstname' => $user->firstname,
				'ltid' => $user->ltid,
				'loancount' => count($user->loans)
			);
		}
		if (Request::ajax()) {
			return Response::json($users);
		}
		return Response::view('users.index', array(
			'users' => $users
		));
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  string  $id
	 * @return Response
	 */
	public function getShow($id)
	{
		# with('loans')->

		if (is_numeric($id)) {
			$user = User::find($id);
		} else {
			$user = User::where('ltid','=',$id)->first();
		}

		if (!$user) {
		    return Response::view('errors.missing', array('what' => 'Brukeren'), 404);
		}
		return Response::view('users.show', array(
				'user' => $user
			));
	}

	/**
	 * Display BIBSYS NCIP info for the specified user.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function getNcipLookup($id)
	{
		$user = User::find($id);
		if (!$user) {
			return Response::json(array('exists' => false));
		}
		$data = $user->ncipLookup();

		return Response::json($data);
	}
	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function getEdit($id)
	{
		$user = User::find($id);
		return Response::view('users.edit', array(
				'user' => $user
			));

	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function postUpdate($id)
	{
		$validator = Validator::make(Input::all(), $this->rules, $this->messages);

		if ($validator->fails())
		{
			return Redirect::action('UsersController@getEdit', $id)
				->withErrors($validator)
				->withInput();
		}

		$user = User::find($id);
		$ltid = Input::get('ltid');
		if (empty($ltid)) {
			$user->ltid = null;
		} else {
			$user->ltid = $ltid;
		}
		$user->lastname = Input::get('lastname');
		$user->firstname = Input::get('firstname');
		$user->phone = Input::get('phone') ? Input::get('phone') : null;
		$user->email = Input::get('email') ? Input::get('email') : null;
		$user->lang = Input::get('lang');
		$user->save();

		return Redirect::action('UsersController@getShow', $id)
			->with('status', 'Informasjonen ble lagret.');
	}

	/**
	 * Display form to merge two users.
	 *
	 * @param  string  $user1
	 * @param  string  $user2
	 * @return Response
	 */
	public function getMerge($user1, $user2)
	{
		$user1 = User::find($user1);
		if (!$user1) {
		    return Response::view('errors.missing', array('what' => 'Bruker 1'), 404);
		}

		$user2 = User::find($user2);
		if (!$user2) {
		    return Response::view('errors.missing', array('what' => 'Bruker 2'), 404);
		}

		$merged = $user1->getMergeData($user2);

		return Response::view('users.merge', array(
			'user1' => $user1,
			'user2' => $user2,
			'merged' => $merged
		));
	}

	/**
	 * Merge $user2 into $user1
	 *
	 * @param  string  $user1
	 * @param  string  $user2
	 * @return Response
	 */
	public function postMerge($user1, $user2)
	{
		$user1 = User::find($user1);
		if (!$user1) {
		    return Response::view('errors.missing', array('what' => 'Bruker 1'), 404);
		}

		$user2 = User::find($user2);
		if (!$user2) {
		    return Response::view('errors.missing', array('what' => 'Bruker 2'), 404);
		}

		$data = array();
		foreach (User::$editableAttributes as $attr) {
			$data[$attr] = Input::get($attr);
		}

		$errors = $user1->merge($user2, $data);

		if (!is_null($errors)) {
			return Redirect::action('UsersController@getMerge', array($user1->id, $user2->id))
				->withErrors($errors);
		}

		return Redirect::action('UsersController@getShow', $user1->id)
			->with('status', 'Brukerne ble flettet.');
	}


}
