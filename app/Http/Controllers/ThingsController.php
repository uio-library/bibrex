<?php

namespace App\Http\Controllers;

use App\Library;
use App\Thing;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ThingsController extends Controller
{

    protected $thing;

    /**
     * Validation error messages.
     *
     * @static array
     */
    protected $messages = [
        'name.required' => 'Internt navn må fylles ut.',
        'name.unique' => 'Typen finnes allerede.',

        'loan_time.required' => 'Lånetid må fylles ut.',

        'name_indefinite_nob.required' => 'Ubestemt form på bokmål må fylles ut.',
        'name_definite_nob.required' => 'Bestemt form på bokmål må fylles ut.',

        'name_indefinite_nno.required' => 'Ubestemt form på nynorsk må fylles ut.',
        'name_definite_nno.required' => 'Bestemt form på nynorsk må fylles ut.',

        'name_indefinite_eng.required' => 'Ubestemt form på engelsk må fylles ut.',
        'name_definite_eng.required' => 'Bestemt form på engelsk må fylles ut.',
    ];

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Response
     */
    public function getIndex(Request $request)
    {
        $libraryId = \Auth::user()->id;

        $things = Thing::query()->with('items.loans');

        if ($request->input('mine')) {
            $things->whereHas('libraries', function ($query) use ($libraryId) {
                $query->where('library_id', '=', $libraryId);
            });
        }

        $things = $things->orderBy('name')
            ->get();

        if ($request->ajax()) {
            return response()->json($things);
        }

        return response()->view('things.index', array(
            'things' => $things
        ));
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Response
     */
    public function json(Request $request)
    {
        $libraryId = \Auth::user()->id;

        $things = Thing::query();

        if ($request->input('withLoans')) {
            $things->with('items.loans');
        }

        if ($request->input('mine')) {
            $things->whereHas('libraries', function ($query) use ($libraryId) {
                $query->where('library_id', '=', $libraryId);
            });
        }

        $things->orderBy('name');

        $things = $things->get()->map(function ($thing) {
            return [
                'id' => $thing->id,
                'type' => 'thing',
                'name' => $thing->name,
                'properties' => $thing->properties,
            ];
        });

        return response()->json($things);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Library $library
     * @return Response
     */
    public function getAvailableJson(Library $library)
    {
        $things = Thing::with('items.loans')
            ->where('library_id', null)
            ->orWhere('library_id', $library->id)
            ->get();

        $out = [];
        foreach ($things as $t) {
            $out[] = [
                'name' => $t->name,
                'available_items' => $t->availableItems(),
            ];
        }

        return response()->json($out);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Library $library
     * @return Response
     */
    public function getAvailable(Library $library)
    {
        return response()->view('things.available', [
            'library_id' => $library->id,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param Thing $thing
     * @return Response
     */
    public function getShow(Thing $thing)
    {
        return response()->view('things.show', array(
            'thing' => $thing,
        ));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Thing $thing
     * @return Response
     */
    public function getEdit(Thing $thing)
    {
        return response()->view('things.edit', array(
            'thing' => $thing,
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Thing $thing
     * @param Request $request
     * @return Response
     */
    public function postUpdate(Thing $thing, Request $request)
    {
        \Validator::make($request->all(), [
            'name' => 'required|unique:things,name' . ($thing->id ? ',' . $thing->id : ''),
            'name_indefinite_nob' => 'required',
            'name_indefinite_nno' => 'required',
            'name_indefinite_eng' => 'required',
            'name_definite_nob' => 'required',
            'name_definite_nno' => 'required',
            'name_definite_eng' => 'required',
            'loan_time' => 'required|numeric|gte:1|lte:36500',
        ], $this->messages)->validate();

        $thing->name = $request->input('name');
        $thing->setProperties([
            'name_indefinite.nob' => $request->input('name_indefinite_nob'),
            'name_definite.nob' => $request->input('name_definite_nob'),
            'name_indefinite.nno' => $request->input('name_indefinite_nno'),
            'name_definite.nno' => $request->input('name_definite_nno'),
            'name_indefinite.eng' => $request->input('name_indefinite_eng'),
            'name_definite.eng' => $request->input('name_definite_eng'),
        ]);
        $thing->loan_time = $request->input('loan_time');
        $thing->note = $request->input('note');

        if (!$thing->save()) {
            return redirect()->action('ThingsController@getEdit', $thing->id ?: '_new')
                ->withErrors($thing->errors)
                ->withInput();
        }

        return redirect()->action('ThingsController@getShow', $thing->id)
            ->with('status', 'Tingen ble lagret!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Thing $thing
     * @return Response
     */
    public function getDestroy(Thing $thing)
    {
        if (count($thing->activeLoans()) != 0) {
            return redirect()->action('ThingsController@getShow', $thing->id)
                ->with('error', 'Kan ikke slette ting med aktive lån.');
        }

        \Log::info(sprintf(
            'Slettet tingen <a href="%s">%s</a>.',
            action('ThingsController@getShow', $thing->id),
            $thing->name
        ));
        $thing->delete();

        return redirect()->action('ThingsController@getShow', $thing->id)
            ->with('status', 'Tingen «' . $thing->name . '» ble slettet.');
    }

    /**
     * Restore the specified resource.
     *
     * @param Thing $thing
     * @return Response
     */
    public function getRestore(Thing $thing)
    {
        $thing->restore();
        \Log::info(sprintf(
            'Gjenopprettet tingen <a href="%s">%s</a>.',
            action('ThingsController@getShow', $thing->id),
            $thing->name
        ));

        return redirect()->action('ThingsController@getShow', $thing->id)
            ->with('status', 'Tingen «' . $thing->name . '» ble gjenopprettet.');
    }

    /**
     * Toggle thing for my library
     *
     * @param Thing $thing
     * @return Response
     */
    public function toggle(Thing $thing, Request $request)
    {
        $libraryId = \Auth::user()->id;

        if ($request->input('value')) {
            $thing->libraries()->attach($libraryId);
            $thing->libraries()->updateExistingPivot($libraryId, [ 'require_item' => true ]);
        } else {
            $thing->libraries()->detach($libraryId);
        }

        return response()->json([
            'status' => 'ok',
            'library_settings' => $thing->library_settings,
        ]);
    }

    /**
     * Toggle setting for my library
     *
     * @param Thing $thing
     * @return Response
     */
    public function updateSetting(Thing $thing, Request $request)
    {
        $libraryId = \Auth::user()->id;
        $thing->libraries()->updateExistingPivot($libraryId, [
            $request->input('key') => $request->input('value'),
        ]);
        return response()->json([
            'status' => 'ok',
            'library_settings' => $thing->library_settings,
        ]);
    }
}
