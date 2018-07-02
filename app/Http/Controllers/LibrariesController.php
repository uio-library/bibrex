<?php

namespace App\Http\Controllers;

use App\Library;
use App\LibraryIp;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class LibrariesController extends Controller
{

    /**
     * Validation error messages.
     *
     * @static array
     */
    protected $messages = array(
        'name.required' => 'Norsk navn må fylles ut',
        'name.unique' => 'Norsk navn må være unikt',
        'name_eng.required' => 'Engelsk navn må fylles ut',
        'name_eng.unique' => 'Engelsk navn må være unikt',
        'email.required' => 'E-post må fylles ut',
        'email.unique' => 'E-post må være unik',
        'email.email' => 'E-post må være en gyldig epostadresse',
        'guest_ltid.regex' => 'LTID må være et gyldig LTID',
    );

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
     * Sets a new password. Note that it does *not store the model*.
     *
     * @param  string    $password
     * @param  string    $passwordRepeated
     * @return bool
     */
    protected function validateAndHashPassword($password, $passwordRepeated)
    {
        if (mb_strlen($password) < 8) {
            throw ValidationException::withMessages([
                'password' => ['Passordet er for kort (kortere enn 8 tegn).'],
            ]);
        }

        if ($password != $passwordRepeated) {
            throw ValidationException::withMessages([
                'password' => ['Du gjentok ikke passordet likt.'],
            ]);
        }

        return \Hash::make($password);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
	public function postStore(Request $request)
	{
        $rules = array(
            'name' => 'required|unique:libraries,name',
            'name_eng' => 'required|unique:libraries,name_eng',
            'email' => 'required|email|unique:libraries,email',
        );
        \Validator::make($request->all(), $rules, $this->messages)->validate();

		$lib = new $this->libFactory();

        $lib->password = $this->validateAndHashPassword($request->input('password'), $request->input('password2'));
		$lib->name = $request->input('name');
        $lib->name_eng = $request->input('name_eng');
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
                $s = Auth::login($lib);
				Session::put('iplogin', true);
                // Session::flash('logged_in_from_ip', true);
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
        $credentials = $request->only('email', 'password');

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

        $lib->password = $this->validateAndHashPassword($request->input('password'), $request->input('password1'));
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
     * @param LibraryIp $ip
     * @return Response
     */
	public function removeIp(LibraryIp $ip)
	{
		$lib = Auth::user();

		if ($ip->library_id != $lib->id) {
			return redirect()->action('LibrariesController@getMyIps')
				->with('status', 'IP-adressen hører ikke til ditt bibliotek.');
		}

		$ip->delete();

		return redirect()->action('LibrariesController@getMyIps')
			->with('status', 'IP-adressen ble fjernet');
	}

}
