<?php

namespace App\Http\Controllers;

use App\Library;
use App\LibraryIp;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LibrariesController extends Controller {

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
		return response()->view('libraries.index', array(
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
		return response()->view('libraries.create', array());
	}

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
	public function postStore(Request $request)
	{
		$lib = new $this->libFactory();

		if (!$lib->setPassword($request->input('password'), $request->input('password2'))) {
			return redirect()->back()
				->withErrors($lib->errors)
				->withInput();
		}

		$lib->name = $request->input('name');
		$lib->email = $request->input('email');

		if (!$lib->save()) {
			return redirect()->back()
				->withErrors($lib->errors)
				->withInput();
		}

		return redirect()->action('LibrariesController@getIndex')
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
			return response()->view('errors.404', array('what' => 'Biblioteket'), 404);
		}
		return response()->view('libraries.show', array(
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
				return redirect()->intended('/');
			}
		}
		return response()->view('login');
	}

    /**
     * Handle an authentication attempt.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postLogin(Request $request)
	{
        $credentials = array(
			'name' => $request->input('library'),
			'password' => $request->input('password')
		);

		if (Auth::attempt($credentials, true)) {
			Session::put('iplogin', false);
			return redirect()->intended('/');
		} else {
			return back()
				->withInput()
				->with('loginfailed', true);
		}
	}

	public function getLogout()
	{
		Auth::logout();
		return redirect()->to('/');
	}

	public function getMyAccount()
	{
		$lib = Auth::user();
		if (!$lib) {
			return response()->view('errors.404', array('what' => 'Biblioteket'), 404);
		}
		return response()->view('libraries.my', array(
			'library' => $lib
		));

	}

	public function postStoreMyAccount(Request $request)
	{
		$lib = Auth::user();
		if (!$lib) {
			return response()->view('errors.404', array('what' => 'Biblioteket'), 404);
		}

		$lib->name = $request->input('name');
		$lib->email = $request->input('email');
		$lib->guest_ltid = $request->input('guest_ltid') ? $request->input('guest_ltid') : null;
		$lib->email = $request->input('email') ? $request->input('email') : null;

		$options = $lib->options;
		$options['guestcard_for_nonworking_cards'] = ($request->input('guestcard_for_nonworking_cards') == 'true');
		$options['guestcard_for_cardless_loans'] = ($request->input('guestcard_for_cardless_loans') == 'true');
		$lib->options = $options;

		if (!$lib->save()) {
			return redirect()->back()
				->withErrors($lib->errors)
				->withInput();
		}

		if ($request->input('password') != '') {
			$password = $request->input('password');
			return redirect()->action('LibrariesController@getPassword')
				->with('password', $password);
		}

		return redirect()->action('LibrariesController@getShow', $lib->id)
			->with('status', 'Kontoinformasjonen ble lagret.');

	}

	public function getPassword()
	{
		$lib = Auth::user();
		if (!$lib) {
			return response()->view('errors.404', array('what' => 'Biblioteket'), 404);
		}
		return response()->view('libraries.password', array(
			'library' => $lib,
			'password' => Session::get('password'),
		));

	}

	public function postPassword(Request $request)
	{
		$lib = Auth::user();
		if (!$lib) {
			return response()->view('errors.404', array('what' => 'Biblioteket'), 404);
		}

		if (!$lib->setPassword($request->input('password'), $request->input('password1'))) {
			return redirect()->back()
				->withErrors($lib->errors)
				->withInput();
		}
		$lib->save();

		return redirect()->action('LibrariesController@getShow', $lib->id)
			->with('status', 'Nytt passord ble satt.');
	}

	/**
	 * Display a listing of the ips.
	 *
	 * @return Response
	 */
	public function getMyIps()
	{

		$lib = Auth::user();
		if (!$lib) {
			return response()->view('errors.404', array('what' => 'Biblioteket'), 404);
		}

		return response()->view('libraries.ips.index', array(
			'library' => $lib
		));

	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function storeIp(Request $request)
	{
		$lib = Auth::user();
		if (!$lib) {
			return response()->view('errors.404', array('what' => 'Biblioteket'), 404);
		}

		$ip = new LibraryIp(array(
			'library_id' => $lib->id,
			'ip' => $request->input('ip')
		));

		if (!$ip->save()) {
			return redirect()->back()
				->withErrors($ip->errors)
				->withInput();
		}

		return redirect()->action('LibrariesController@getMyIps')
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
			return response()->view('errors.404', array('what' => 'Biblioteket'), 404);
		}

		$ip = LibraryIp::find($id);
		if ($ip->library_id != $lib->id) {
			return redirect()->action('LibrariesController@getMyIps')
				->with('status', 'IP-adressen hører ikke til ditt bibliotek.');
		}

		$ip->delete();

		return redirect()->action('LibrariesController@getMyIps')
			->with('status', 'IP-adressen ble fjernet');
	}

}
