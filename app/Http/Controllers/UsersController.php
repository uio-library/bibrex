<?php

namespace App\Http\Controllers;

use App\User;
use App\Alma\User as AlmaUser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use LimitIterator;
use Scriptotek\Alma\Client as AlmaClient;

class UsersController extends Controller
{

    private $messages = [
        'barcode.regex' => 'Strekkoden er ikke på riktig format.',
        'barcode.unique' => 'Det finnes allerede en annen bruker med denne strekkoden. ' .
            'Du kan eventuelt slå dem sammen i brukeroversikten.',
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
        $users = [];
        foreach (User::with('loans')->orderBy('lastname')->get() as $user) {
            $users[] = array(
                'id' => $user->id,
                'primaryId' => $user->alma_primary_id,
                'group' => $user->alma_user_group,
                'name' => $user->lastname . ', ' . $user->firstname,
                'barcode' => $user->barcode,
                'type' => 'local',
            );
        }

        if ($request->ajax()) {
            return response()->json($users);
        }

        return response()->view('users.index', [
            'users' => $users,
        ]);
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
            return back()->with('error', 'Du må registrere Låne-ID for brukeren før du kan importere.');
        }
        $query = 'ALL~' . $user->barcode;
        $users = collect($alma->users->search($query, ['limit' => 1]))->map(function ($u) {
            return new AlmaUser($u);
        });
        if (!count($users)) {
            return back()->with('error', 'Brukeren ble ikke funnet i Alma. Kanskje hen har fått ny låntaker-ID?');
        }

        $user->mergeFromAlmaResponse($users[0]);
        $user->save();

        return back()->with('status', 'Brukeropplysninger ble oppdatert fra Alma.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param User $user
     * @return Response
     */
    public function getEdit(User $user)
    {
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
    public function putUpdate(User $user, Request $request)
    {
        \Validator::make($request->input(), [
            'barcode' => 'nullable|regex:/^[0-9a-zA-Z]{10}$/|unique:users,barcode' . ($user->id ? ',' . $user->id : ''),
            'lastname' => 'required',
            'firstname' => 'required',
            'email' => 'requiredWithout:phone',
            'lang' => 'required',
        ], $this->messages)->validate();

        $user->barcode = $request->input('barcode');
        $user->lastname = $request->input('lastname');
        $user->firstname = $request->input('firstname');
        $user->phone = $request->input('phone');
        $user->email = $request->input('email');
        $user->note = $request->input('note');
        $user->lang = $request->input('lang');
        if (!$user->save()) {
            dd('Oi');
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
            ->with('status', "Brukeren $name ble slettet.");
    }
}
