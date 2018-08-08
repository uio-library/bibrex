<?php

namespace App\Http\Controllers;

use App\Library;
use App\LibraryIp;
use App\Rules\TemporaryBarcodeExists;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Scriptotek\Alma\Client as AlmaClient;

class LibrariesController extends Controller
{

    /**
     * Validation error messages.
     *
     * @static array
     */
    protected $messages = [
        'name.required' => 'Norsk navn må fylles ut',
        'name.unique' => 'Norsk navn må være unikt',
        'name_eng.required' => 'Engelsk navn må fylles ut',
        'name_eng.unique' => 'Engelsk navn må være unikt',
        'email.required' => 'E-post må fylles ut',
        'email.unique' => 'E-post må være unik',
        'email.email' => 'E-post må være en gyldig epostadresse',
        'guest_ltid.regex' => 'LTID må være et gyldig LTID',
        'ip.required' => 'Adressen er tom',
        'ip.unique' => 'Adressen må være unik',
        'ip.ip' => 'Ugyldig ip-adresse',
    ];

    protected $lib;

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
     * @param AlmaClient $alma
     * @return Response
     */
    public function postStore(Request $request, AlmaClient $alma)
    {
        $temporaryBarcode = new TemporaryBarcodeExists($alma);

        $rules = array(
            'name' => 'required|unique:libraries,name',
            'name_eng' => 'required|unique:libraries,name_eng',
            'email' => 'required|email|unique:libraries,email',
            'library_code' => 'sometimes|nullable|unique:libraries,library_code',
            'temporary_barcode' => [$temporaryBarcode],
        );
        \Validator::make($request->all(), $rules, $this->messages)->validate();

        $lib = new Library();
        $lib->password = $this->validateAndHashPassword($request->input('password'), $request->input('password2'));
        $lib->name = $request->input('name');
        $lib->name_eng = $request->input('name_eng');
        $lib->email = $request->input('email');
        $lib->library_code = $request->input('library_code');
        $lib->temporary_barcode = $temporaryBarcode->getNormalizedValue();

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
     * @param Library $library
     * @return Response
     */
    public function getShow(Library $library)
    {
        return response()->view('libraries.show', [
            'library' => $library
        ]);
    }

    public function getLogin()
    {
        if (isset($_SERVER)) {
            $library_ip = LibraryIp::whereRaw('? LIKE ip', array(array_get($_SERVER, 'REMOTE_ADDR', '')))->first();
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
        return response()->view('libraries.my', array(
            'library' => Auth::user(),
        ));
    }

    public function postStoreMyAccount(Request $request, AlmaClient $alma)
    {
        $library = Auth::user();

        $temporaryBarcode = new TemporaryBarcodeExists($alma);

        $rules = array(
            'name' => 'required|unique:libraries,name,' . $library->id,
            'name_eng' => 'required|unique:libraries,name_eng,' . $library->id,
            'email' => 'required|email|unique:libraries,email,' . $library->id,
            'library_code' => 'sometimes|nullable|unique:libraries,library_code,' . $library->id,
            'temporary_barcode' => [$temporaryBarcode],
        );
        \Validator::make($request->all(), $rules, $this->messages)->validate();

        $library->name = $request->input('name');
        $library->email = $request->input('email');
        $library->guest_ltid = $request->input('guest_ltid');
        $library->email = $request->input('email');
        $library->library_code = $request->input('library_code');
        $library->temporary_barcode = $temporaryBarcode->getNormalizedValue();

        if (!$library->save()) {
            return redirect()->back()
                ->withErrors($library->errors)
                ->withInput();
        }

        if ($request->input('password') != '') {
            $password = $request->input('password');
            return redirect()->action('LibrariesController@getPassword')
                ->with('password', $password);
        }

        return redirect()->action('LibrariesController@getShow', $library->id)
            ->with('status', 'Kontoinformasjonen ble lagret.');
    }

    public function getPassword()
    {
        $library = Auth::user();
        return response()->view('libraries.password', array(
            'library' => $library,
            'password' => Session::get('password'),
        ));
    }

    public function postPassword(Request $request)
    {
        $library = Auth::user();
        $library->password = $this->validateAndHashPassword($request->input('password'), $request->input('password1'));
        $library->save();

        return redirect()->action('LibrariesController@getShow', $library->id)
            ->with('status', 'Nytt passord ble satt.');
    }

    /**
     * Display a listing of the ips.
     *
     * @return Response
     */
    public function getMyIps()
    {
        return response()->view('libraries.ips.index', [
            'library' => Auth::user(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function storeIp(Request $request)
    {
        \Validator::make($request->all(), [
            'ip' => ['required', 'ip', 'unique:library_ips,ip'],
        ])->validate();

        $newIp = $request->input('ip');

        LibraryIp::create([
            'library_id' => Auth::user()->id,
            'ip' => $newIp,
        ]);

        return redirect()->action('LibrariesController@getMyIps')
            ->with('status', "IP-adressen $newIp ble lagt til");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param LibraryIp $ip
     * @return Response
     */
    public function removeIp(LibraryIp $ip)
    {
        if ($ip->library_id !== Auth::user()->id) {
            return redirect()->action('LibrariesController@getMyIps')
                ->with('status', 'IP-adressen hører ikke til ditt bibliotek.');
        }

        $ip->delete();

        return redirect()->action('LibrariesController@getMyIps')
            ->with('status', 'IP-adressen ble fjernet');
    }
}
