<?php

namespace App\Http\Controllers;

use App\User;
use App\Alma\User as AlmaUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use LimitIterator;
use Scriptotek\Alma\Client as AlmaClient;

class UsersController extends Controller
{

    private $messages = [
        'barcode.regex' => 'Låne-ID er ikke på riktig format.',
        'university_id.regex' => 'Feide-ID er ikke på riktig format (brukernavn@institusjon.no).',
        'barcode.unique' => 'Det finnes allerede en annen bruker med denne strekkoden.',
        'university_id.unique' => 'Det finnes allerede en annen bruker med denne Feide-IDen.',
        'lastname.required' => 'Etternavn må fylles inn.',
        'firstname.required' => 'Fornavn må fylles inn.',
        'email.required_without' => 'Enten e-post eller telefonnummer må fylles inn.',
        'lang.required' => 'Språk må fylles inn.'
    ];

    /**
     * Display a listing of the resource.
     *
     * @param  Request $request
     * @return Response
     */
    public function getIndex(Request $request)
    {
        $users = User::with('loans')
            ->where('lastname', '!=', '(anonymisert)')
            ->orderBy('lastname')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'primaryId' => $user->alma_primary_id,
                    'group' => $user->alma_user_group,
                    'name' => $user->lastname . ', ' . $user->firstname,
                    'barcode' => $user->barcode,
                    'in_alma' => $user->in_alma,
                    'created_at' => $user->created_at->toDateTimestring(),
                    'note' => $user->note,
                ];
            });

        return response()->view('users.index', [
            'users' => $users,
        ]);
    }

    /**
     * Display a listing of the resource as json.
     *
     * @param  Request $request
     * @return Response
     */
    public function json(Request $request)
    {
        $users = [];
        foreach (User::get() as $user) {
            $users[] = [
                'id' => $user->id,
                'primaryId' => $user->alma_primary_id,
                'group' => $user->alma_user_group,
                'name' => $user->lastname . ', ' . $user->firstname,
                'barcode' => $user->barcode,
                'type' => 'local',
            ];
        }

        return response()->json($users);
    }

    /**
     * Display a listing of the resource.
     *
     * @param AlmaClient $alma
     * @param  Request $request
     * @return Response
     */
    public function searchAlma(AlmaClient $alma, Request $request)
    {
        if (is_null($alma->key)) {
            \Log::warning('Cannot search Alma users since no Alma API key is configured.');
            return response()->json([]);
        }
        $query = 'ALL~' . $request->input('query');
        $users = collect($alma->users->search($query, ['limit' => 5]))->map(function ($u) {
            return new AlmaUser($u);
        });

        return response()->json($users);
    }

    /**
     * Display the specified resource.
     *
     * @param User $user
     * @return Response
     */
    public function getShow(User $user)
    {
        // if (is_numeric($id)) {
        //  $user = User::find($id);
        // } else {
        //  $user = User::where('ltid','=',$id)->first();
        // }

        if (!$user) {
            return response()->view('errors.404', array('what' => 'Brukeren'), 404);
        }
        return response()->view('users.show', array(
                'user' => $user
            ));
    }

    /**
     * Display BIBSYS NCIP info for the specified user.
     *
     * @param  int  $id
     * @return Response
     */
    public function getNcipLookup(AlmaClient $alma, User $user)
    {
        if (!$user->barcode) {
            return back()->with('error', 'Du må registrere låne-ID for brukeren før du kan importere.');
        }
        $query = 'ALL~' . $user->barcode;
        $users = collect($alma->users->search($query, ['limit' => 1]))->map(function ($u) {
            return new AlmaUser($u);
        });
        if (!count($users)) {
            return back()->with('error', 'Fant ikke låne-ID-en ' . $user->barcode . ' i Alma 😭 ');
        }

        $user->mergeFromAlmaResponse($users[0]);
        $user->save();

        return back()->with('status', 'Brukeropplysninger ble oppdatert fra Alma.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param User $user
     * @param Request $request
     * @return Response
     */
    public function getEdit(User $user, Request $request)
    {
        if (!$user->id) {
            $user->barcode = $request->barcode;
            $user->university_id = $request->university_id;
            $user->lastname = $request->lastname;
            $user->firstname = $request->firstname;
            $user->phone = $request->phone;
            $user->email = $request->email;
        }

        return response()->view('users.edit', array(
            'user' => $user
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param User $user
     * @param  Request $request
     * @return Response
     */
    public function upsert(User $user, Request $request)
    {
        \Validator::make($request->input(), [
            'barcode' => 'nullable|regex:/^[0-9a-zA-Z]{10}$/|unique:users,barcode' . ($user->id ? ',' . $user->id : ''),
            'university_id' => 'nullable|regex:/@/|unique:users,university_id' . ($user->id ? ',' . $user->id : ''),
            'lastname' => 'required',
            'firstname' => 'required',
            'email' => 'requiredWithout:phone',
            'lang' => 'required',
        ], $this->messages)->validate();

        $user->barcode = $request->input('barcode');
        $user->university_id = $request->input('university_id');
        $user->lastname = $request->input('lastname');
        $user->firstname = $request->input('firstname');
        $user->phone = $request->input('phone');
        $user->email = $request->input('email');
        $user->note = $request->input('note');
        $user->lang = $request->input('lang');
        $user->last_loan_at = Carbon::now();
        $newUser = !$user->exists;
        if (!$user->save()) {
            dd('Oi');
        }

        if ($newUser) {
            return redirect()->action('LoansController@getIndex')
                ->with('status', 'Brukeren ble opprettet.')
                ->with('user', [
                    'type' => 'local',
                    'id' => $user->id,
                    'name' => $user->lastname . ', ' . $user->firstname,
                ]);
        }

        return redirect()->action('UsersController@getShow', $user->id)
            ->with('status', 'Brukeren ble lagret.');
    }

    /**
     * Display form to merge two users.
     *
     * @param User $user1
     * @param User $user2
     * @return Response
     */
    public function getMerge(User $user1, User $user2)
    {
        $merged = $user1->getMergeData($user2);

        return response()->view('users.merge', array(
            'user1' => $user1,
            'user2' => $user2,
            'merged' => $merged
        ));
    }

    /**
     * Merge $user2 into $user1
     *
     * @param Request $request
     * @param User $user1
     * @param User $user2
     * @return Response
     */
    public function postMerge(Request $request, User $user1, User $user2)
    {
        $mergedAttributes = array();
        foreach (User::$editableAttributes as $attr) {
            $mergedAttributes[$attr] = $request->input($attr);
        }

        $errors = $user1->merge($user2, $mergedAttributes);

        if (!is_null($errors)) {
            return redirect()->action('UsersController@getMerge', array($user1->id, $user2->id))
                ->withErrors($errors);
        }

        return redirect()->action('UsersController@getShow', $user1->id)
            ->with('status', 'Brukerne ble flettet.');
    }

    /**
     * Show the form for creating the specified resource.
     *
     * @param Request $request
     * @return Response
     */
    public function createForm(Request $request)
    {
        $user = User::make();

        return response()->view('users.create', [
            'user' => $user,
        ]);
    }

    /**
     * Show the form for deleting the specified resource.
     *
     * @param User $user
     * @param Request $request
     * @return Response
     */
    public function deleteForm(User $user, Request $request)
    {
        if ($user->loans()->count()) {
            return redirect()->action('UsersController@getShow', $user->id)
                ->with('error', 'Kan ikke slette en bruker med aktive lån.');
        }

        return response()->view('users.delete', [
            'user' => $user,
        ]);
    }

    /**
     * Delte the specified resource from storage.
     *
     * @param User $user
     * @param Request $request
     * @return Response
     */
    public function delete(User $user, Request $request)
    {
        if ($user->loans()->count()) {
            return redirect()->action('UsersController@getShow', $user->id)
                ->with('error', 'Kan ikke slette en bruker med aktive lån.');
        }

        $user_id = $user->id;
        $name = $user->name;

        $user->delete();
        \Log::info(sprintf('Slettet brukeren med ID %d', $user_id));

        return redirect()->action('UsersController@getIndex')
            ->with('status', "Brukeren $name ble slettet (men slapp av, du har ikke drept noen).");
    }
}
