<?php

class LibrariesController extends BaseController {

	protected $lib;

	public function __construct(Library $lib)
	{
		$this->libFactory = $lib;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getIndex()
	{
		$items = Library::with('ips')->get();
		return Response::view('libraries.index', array(
			'libraries' => $items
		));
	}

	/**
	 * Display a form to create the resource.
	 *
	 * @return Response
	 */
	public function getCreate()
	{
		return Response::view('libraries.create', array());
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function postStore()
	{
		$lib = new $this->libFactory();

		if (!$lib->setPassword(Input::get('password'), Input::get('password2'))) {
			return Redirect::back()
				->withErrors($lib->errors)
				->withInput();
		}

		$lib->name = Input::get('name');
		$lib->email = Input::get('email');

		if (!$lib->save()) {
			return Redirect::back()
				->withErrors($lib->errors)
				->withInput();
		}

		return Redirect::action('LibrariesController@getIndex')
			->with('status', 'Biblioteket ble opprettet!');
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  string  $id
	 * @return Response
	 */
	public function getShow($id)
	{
		$lib = $this->libFactory->find($id);
		if (!$lib) {
			return Response::view('errors.missing', array('what' => 'Biblioteket'), 404);
		}
		return Response::view('libraries.show', array(
			'library' => $lib
		));
	}

	function getLogin()
	{
		if (isset($_SERVER)) {
			$library_ip = LibraryIp::whereRaw('? LIKE ip', array(array_get($_SERVER, 'REMOTE_ADDR','')))->first();
			if ($library_ip) {
				$lib = $library_ip->library;
				Auth::loginUsingId($lib->id);
				Session::put('iplogin', true);
				//Session::flash('logged_in_from_ip', true);
				return Redirect::intended('/');
			}
		}
		return Response::view('login');
	}

	public function postLogin()
	{

		$credentials = [
			'name' => Input::get('library'),
			'password' => Input::get('password')
		];

		if (Auth::attempt($credentials, true)) {
			Session::put('iplogin', false);
			return Redirect::intended('/');
		} else {
			return Redirect::back()
				->withInput()
				->with('loginfailed',true);
		}
	}

	public function getLogout()
	{
		Auth::logout();
		return Redirect::to('/');
	}

	public function myAccount()
	{
		$lib = Auth::user();
		if (!$lib) {
			return Response::view('errors.missing', array('what' => 'Biblioteket'), 404);
		}
		return Response::view('libraries.my', array(
			'library' => $lib
		));

	}

	public function postStoreMyAccount()
	{
		$lib = Auth::user();
		if (!$lib) {
			return Response::view('errors.missing', array('what' => 'Biblioteket'), 404);
		}

		$lib->name = Input::get('name');
		$lib->email = Input::get('email');
		$lib->guest_ltid = Input::get('guest_ltid') ? Input::get('guest_ltid') : null;
		$lib->email = Input::get('email') ? Input::get('email') : null;

		$options = $lib->options;
		$options['guestcard_for_nonworking_cards'] = (Input::get('guestcard_for_nonworking_cards') == 'true');
		$options['guestcard_for_cardless_loans'] = (Input::get('guestcard_for_cardless_loans') == 'true');
		$lib->options = $options;

		if (!$lib->save()) {
			return Redirect::back()
				->withErrors($lib->errors)
				->withInput();
		}

		if (Input::get('password') != '') {
			$password = Input::get('password');
			return Redirect::action('LibrariesController@getPassword')
				->with('password', $password);
		}

		return Redirect::action('LibrariesController@getShow', $lib->id)
			->with('status', 'Kontoinformasjonen ble lagret.');

	}

	public function getPassword()
	{
		$lib = Auth::user();
		if (!$lib) {
			return Response::view('errors.missing', array('what' => 'Biblioteket'), 404);
		}
		return Response::view('libraries.password', array(
			'library' => $lib,
			'password' => Session::get('password'),
		));

	}

	public function postPassword()
	{
		$lib = Auth::user();
		if (!$lib) {
			return Response::view('errors.missing', array('what' => 'Biblioteket'), 404);
		}

		if (!$lib->setPassword(Input::get('password'), Input::get('password1'))) {
			return Redirect::back()
				->withErrors($lib->errors)
				->withInput();
		}
		$lib->save();

		return Redirect::action('LibrariesController@getShow', $lib->id)
			->with('status', 'Nytt passord ble satt.');
	}

	/**
	 * Display a listing of the ips.
	 *
	 * @return Response
	 */
	public function myIps()
	{

		$lib = Auth::user();
		if (!$lib) {
			return Response::view('errors.missing', array('what' => 'Biblioteket'), 404);
		}

		return Response::view('libraries.ips.index', array(
			'library' => $lib
		));

	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function storeIp()
	{
		$lib = Auth::user();
		if (!$lib) {
			return Response::view('errors.missing', array('what' => 'Biblioteket'), 404);
		}

		$ip = new LibraryIp(array(
			'library_id' => $lib->id,
			'ip' => Input::get('ip')
		));

		if (!$ip->save()) {
			return Redirect::back()
				->withErrors($ip->errors)
				->withInput();
		}

		return Redirect::action('LibrariesController@myIps')
			->with('status', 'IP-adressen ble lagt til');
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function removeIp($id)
	{
		$lib = Auth::user();
		if (!$lib) {
			return Response::view('errors.missing', array('what' => 'Biblioteket'), 404);
		}

		$ip = LibraryIp::find($id);
		if ($ip->library_id != $lib->id) {
			return Redirect::action('LibrariesController@myIps')
				->with('status', 'IP-adressen hører ikke til ditt bibliotek.');
		}

		$ip->delete();

		return Redirect::action('LibrariesController@myIps')
			->with('status', 'IP-adressen ble fjernet');
	}

}
