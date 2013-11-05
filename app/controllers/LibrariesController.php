<?php

class LibrariesController extends BaseController {

	function getLogin()
	{
		$library_ip = LibraryIp::whereRaw('? LIKE ip', array(getenv('REMOTE_ADDR')))->first();
		if ($library_ip) {
			$lib = $library_ip->library;
			Auth::loginUsingId($lib->id);
			Session::put('iplogin', true);
			//Session::flash('logged_in_from_ip', true);
			return Redirect::intended('/');
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

	public function getShow($id)
	{
		$lib = Library::find($id);
		if (!$lib) {
			return Response::view('errors.missing', array('what' => 'Biblioteket'), 404);
		}
		return Response::view('libraries.show', array(
			'library' => $lib
		));

	}

	public function getMy()
	{
		$lib = Auth::user();
		if (!$lib) {
			return Response::view('errors.missing', array('what' => 'Biblioteket'), 404);
		}
		return Response::view('libraries.my', array(
			'library' => $lib
		));

	}

	public function postMy()
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
			->with('status', 'Informasjonen ble lagret.');

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

		if (Input::get('password1') != Input::get('password')) {
			return Redirect::back()
				->withInput();
		}

		$lib->password = Hash::make(Input::get('password'));
		$lib->save();

		return Redirect::action('LibrariesController@getShow', $lib->id)
			->with('status', 'Nytt passord ble satt.');

	}

}
