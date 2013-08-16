<?php

class UsersController extends BaseController {

	/**
	 * The layout that should be used for responses.
	 */
	protected $layout = 'layouts.master';

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
		if (Request::ajax()) {
			$users = array();
			foreach (User::all() as $user) {
				$users[] = array(
					'id' => $user->id,
					'value' => $user->lastname . ', ' . $user->firstname,
					'lastname' => $user->lastname,
					'firstname' => $user->firstname,
					'ltid' => $user->ltid
				);
			}
			return Response::json($users);

		} else {
			$users = User::with('loans')->get();
			$this->layout->content = View::make('users.index')
				->with('users', $users);			
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function getShow($id)
	{
		# with('loans')->
		$user = User::find($id);
		$this->layout->content = View::make('users.show')
			->with('user', $user);
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
		$ncip = new Ncip('http://ncip.bibsys.no/ncip/NCIPResponder');
		$data = $ncip->lookupUser($user->ltid);

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
		$this->layout->content = View::make('users.edit')
			->with('user', $user);
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
		$user->ltid = Input::get('ltid');
		$user->lastname = Input::get('lastname');
		$user->firstname = Input::get('firstname');
		$user->phone = Input::get('phone') ? Input::get('phone') : null;
		$user->email = Input::get('email') ? Input::get('email') : null;
		$user->lang = Input::get('lang');
		$user->save();

		return Redirect::action('UsersController@getShow', $id)
			->with('status', 'Informasjonen ble lagret.');
	}

}