<?php

namespace App\Http\Controllers;

use App\Alma\AlmaUsers;
use App\Alma\User as AlmaUser;
use App\User;
use App\UserIdentifier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use LimitIterator;
use Scriptotek\Alma\Client as AlmaClient;

class UsersController extends Controller
{

    private $messages = [
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
        $users = User::with('loans', 'identifiers')
            ->where('lastname', '!=', '(anonymisert)')
            ->orderBy('lastname')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'primaryId' => $user->alma_primary_id,
                    'group' => $user->alma_user_group,
                    'name' => $user->lastname . ', ' . $user->firstname,
                    'identifiers' => $user->getAllIdentifierValues(),
                    'in_alma' => $user->in_alma,
                    'created_at' => $user->created_at->toDateTimestring(),
                    'note' => $user->note,
                    'blocks' => $user->blocks,
                    'fees' => $user->fees,
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
        foreach (User::with('identifiers')->get() as $user) {
            $users[] = [
                'id' => $user->alma_primary_id ?? $user->id,
                // 'primaryId' => $user->alma_primary_id,
                'group' => $user->alma_user_group,
                'name' => $user->lastname . ', ' . $user->firstname,
                'identifiers' => $user->identifiers,
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
        $users = collect($alma->users->search($query, ['limit' => 5]))->map(function ($result) {
            return [
                'id' => $result->primary_id,
                'name' => "{$result->last_name}, {$result->first_name}",
            ];
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
        return response()->view('users.show', [
            'user' => $user,
        ]);
    }

    /**
     * Display form for connecting local user to external user.
     *
     * @param User $user
     * @return Response
     */
    public function connectForm(User $user)
    {
        $ident = $user->identifiers()->first();
        return response()->view('users.connect', [
            'user' => $user,
            'user_identifier' => is_null($ident) ? null : $ident->value,
        ]);
    }

    /**
     * Connect local user to external user.
     *
     * @param AlmaUsers $almaUsers
     * @param Request $request
     * @param User $user
     * @return Response
     */
    public function connect(AlmaUsers $almaUsers, Request $request, User $user)
    {
        $identifier = $request->identifier;
        if (empty($identifier)) {
            return back()->with('error', 'Du må registrere låne-ID.');
        }

        $other = User::fromIdentifier($identifier);
        if (!is_null($other) && $other->id != $user->id) {
            return back()->with('error', 'Låne-ID-en er allerede koblet til en annen Bibrex-bruker ' .
                '(' . $other->name . '). Du kan slå dem sammen fra brukeroversikten.');
        }

        $almaUser = $almaUsers->findById($identifier);

        if (!$almaUser) {
            return back()->with('error', 'Fant ikke noen bruker med identifikator ' . $identifier . ' i Alma 😭 ');
        }

        try {
            $almaUsers->updateLocalUserFromAlmaUser($user, $almaUser);
        } catch (\RuntimeException $ex) {
            return back()->with('error', $ex->getMessage());
        }
        $user->save();

        return redirect()->action('UsersController@getShow', $user->id)
            ->with('status', 'Bibrex-brukeren ble koblet med Alma-brukeren!');
    }

    /**
     * Import user data from Alma.
     *
     * @param AlmaUsers $almaUsers
     * @param User $user
     * @return Response
     */
    public function sync(AlmaUsers $almaUsers, User $user)
    {
        if (!$user->alma_primary_id && !$user->identifiers->count()) {
            return back()->with('error', 'Du må registrere minst én identifikator for brukeren før du kan importere.');
        }

        if (!$almaUsers->updateLocalUserFromAlmaUser($user)) {
            $user->save();

            return back()->with('error', 'Fant ikke brukeren i Alma 😭');
        }
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
            $identifiers = [];
            if ($request->barcode) {
                $identifiers[] = UserIdentifier::make([
                    'value' => $request->barcode,
                    'type' => 'barcode',
                ]);
            }
            if ($request->university_id) {
                $identifiers[] = UserIdentifier::make([
                    'value' => $request->university_id,
                    'type' => 'university_id',
                ]);
            }
            $user->identifiers = $identifiers;
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
     * @param Request $request
     * @return Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function upsert(User $user, Request $request)
    {
        //  TODO: Move to a new UserUpsertRequest --------------------------------------------------------------------

        $validators = [
            'lastname' => 'required',
            'firstname' => 'required',
            'email' => 'requiredWithout:phone',
            'lang' => 'required',
        ];

        $identifiers = [];
        $messages = $this->messages;
        foreach ($request->all() as $key => $val) {
            if (preg_match('/identifier_type_(new|[0-9]+)/', $key, $matches)) {
                $identifierId = $matches[1];

                if (empty($request->{"identifier_value_{$identifierId}"})) {
                    continue;
                }

                $identifiers[] = [
                    'type' => $request->{"identifier_type_{$identifierId}"},
                    'value' => $request->{"identifier_value_{$identifierId}"},
                ];

                if (!empty($request->{"identifier_value_{$identifierId}"})) {
                    $validators["identifier_value_{$identifierId}"] =
                        'unique:user_identifiers,value' . ($identifierId == 'new' ? '' : ",$identifierId");
                    $validators["identifier_type_{$identifierId}"] = 'in:barcode,university_id';
                    $messages["identifier_value_{$identifierId}.unique"] =
                        'Det finnes allerede en annen bruker med denne identifikatoren.';
                    $messages["identifier_type_{$identifierId}.in"] = 'Identifikatoren har ugyldig type.';
                }
            }
        }

        \Validator::make($request->input(), $validators, $messages)->validate();

        // ------------------------------------------------------------------------------------------------

        $user->lastname = $request->input('lastname');
        $user->firstname = $request->input('firstname');
        $user->phone = $request->input('phone');
        $user->email = $request->input('email');
        $user->note = $request->input('note');
        $user->lang = $request->input('lang');
        $user->last_loan_at = Carbon::now();
        $newUser = !$user->exists;
        if (!$user->save()) {
            throw new \RuntimeException('Ukjent feil under lagring av bruker!');
        }

        // Defer uniqueness check in case values are swapped

        $user->setIdentifiers($identifiers);

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

        $mergedAttributes['identifiers'] = [];
        foreach ($request->all() as $key => $val) {
            if (preg_match('/identifier_type_([0-9]+)/', $key, $matches)) {
                $identifierId = $matches[1];

                if (empty($request->{"identifier_value_{$identifierId}"})) {
                    continue;
                }

                $mergedAttributes['identifiers'][] = [
                    'type' => $request->{"identifier_type_{$identifierId}"},
                    'value' => $request->{"identifier_value_{$identifierId}"},
                ];
            }
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
        \Log::info(sprintf('Slettet brukeren %s (ID %d)', $name, $user_id));

        return redirect()->action('UsersController@getIndex')
            ->with('status', "Brukeren $name ble slettet (men slapp av, du har ikke drept noen).");
    }
}
