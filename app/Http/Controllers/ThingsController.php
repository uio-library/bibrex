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

        'properties.name_indefinite.nob.required' => 'Ubestemt form på bokmål må fylles ut.',
        'properties.name_definite.nob.required' => 'Bestemt form på bokmål må fylles ut.',

        'properties.name_indefinite.nno.required' => 'Ubestemt form på nynorsk må fylles ut.',
        'properties.name_definite.nno.required' => 'Bestemt form på nynorsk må fylles ut.',

        'properties.name_indefinite.eng.required' => 'Ubestemt form på engelsk må fylles ut.',
        'properties.name_definite.eng.required' => 'Bestemt form på engelsk må fylles ut.',
    ];

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $libraryId = \Auth::user()->id;

        $things = Thing::with('items', 'settings', 'items.loans')
            ->orderBy('name')
            ->get();

        $things = $things->map(function ($thing) use ($libraryId) {
                $items = $thing->items->whereNotIn('dokid', [null]);
                return [
                    'type' => 'thing',
                    'id' => $thing->id,
                    'name' => $thing->name,
                    'library_settings' => $thing->library_settings,
                    'properties' => $thing->properties,
                    'loan_time' => $thing->loan_time,
                    'items_total' => $items->count(),
                    'items_mine' => $items->where('library_id', $libraryId)->count(),
                ];
        });

        return response()->view('things.index', [
            'things' => $things,
        ]);
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

        $things = Thing::with('settings');

        if ($request->input('withLoans')) {
            $things->with('items.loans');
        }

        $things = $things->orderBy('name')->get();

        if ($request->input('withoutBarcode')) {
            $things = $things->filter(function ($thing) {
                return $thing->library_settings->loans_without_barcode;
            })->values();
        }

        $things = $things->map(function ($thing) {
            return [
                'id' => $thing->id,
                'type' => 'thing',
                'name' => $thing->name,
                'properties' => $thing->properties,
                'library_settings' => $thing->library_settings,
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
    public function show(Thing $thing)
    {
        return response()->view('things.show', array(
            'thing' => $thing,
        ));
    }

    /**
     * Update or create the specified resource in storage.
     *
     * @param Thing $thing
     * @param Request $request
     * @return Response
     */
    public function upsert(Thing $thing, Request $request)
    {
        \Validator::make($request->all(), [
            'name' => 'required|unique:things,name' . ($thing->id ? ',' . $thing->id : ''),
            'properties.name_indefinite.nob' => 'required',
            'properties.name_indefinite.nno' => 'required',
            'properties.name_indefinite.eng' => 'required',
            'properties.name_definite.nob' => 'required',
            'properties.name_definite.nno' => 'required',
            'properties.name_definite.eng' => 'required',
            'properties.loan_time' => 'required|numeric|min:1',
        ], $this->messages)->validate();

        $thing->name = $request->input('name');
        $thing->properties = $request->input('properties');
        $thing->save();

        return response()->json([
            'status' => 'Tingen «' . $thing->name . '» ble lagret.',
            'thing' => $thing,
        ]);
    }

    /**
     * Update thing settings for my library.
     *
     * @param Thing $thing
     * @return Response
     */
    public function updateSettings(Thing $thing, Request $request)
    {
        \Validator::make($request->all(), [
            'loans_without_barcode' => ['required', 'boolean'],
            'reminders' => ['required', 'boolean'],
        ])->validate();

        $settings = $thing->library_settings;
        $settings->loans_without_barcode = (boolean) $request->input('loans_without_barcode');
        $settings->reminders = (boolean) $request->input('reminders');
        $settings->save();

        return response()->json([
            'status' => 'ok',
            'library_settings' => $settings,
        ]);
    }

    public function miscErrorResponse($msg)
    {
        return response()->json([
            'errors' => ['misc' => [$msg]],
        ], 422);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Thing $thing
     * @return Response
     */
    public function delete(Thing $thing)
    {
        if ($thing->items()->count() != 0) {
            return $this->miscErrorResponse('Kan ikke slette ting med eksemplarer.');
        }

        \Log::info(sprintf(
            'Slettet tingen <a href="%s">%s</a>.',
            action('ThingsController@show', $thing->id),
            $thing->name
        ));
        $thing->delete();

        return response()->json([
            'status' => 'Tingen «' . $thing->name . '» ble sletta.'
        ]);
    }

    /**
     * Restore the specified resource.
     *
     * @param Thing $thing
     * @return Response
     */
    public function restore(Thing $thing)
    {
        $thing->restore();
        \Log::info(sprintf(
            'Gjenopprettet tingen <a href="%s">%s</a>.',
            action('ThingsController@show', $thing->id),
            $thing->name
        ));

        return response()->json([
            'status' => 'Tingen «' . $thing->name . '» ble gjenopprettet.'
        ]);
    }
}
