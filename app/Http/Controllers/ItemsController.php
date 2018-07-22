<?php

namespace App\Http\Controllers;

use App\Item;
use App\Support\DbHelper;
use App\Thing;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Rule;

class ItemsController extends Controller
{

    /**
     * Validation error messages.
     *
     * @static array
     */
    protected $messages = [
        'barcode.required' => 'Strekkode må legges inn.',
        'barcode.unique' => 'Strekkoden er allerede i bruk. Du må legge inn en unik strekkode.',
    ];

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $items = Item::with('loans', 'thing')
            ->whereNotNull('barcode')
            ->where('library_id', '=', \Auth::user()->id);

        return response()->view('items.index', [
            'items' => $items->get()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'thing' => $item->thing->name,
                    'barcode' => $item->barcode,
                    'note' => $item->note,
                    'loan' => $item->loans()->first(),
                ];
            }),
        ]);
    }

    /**
     * Search for resources.
     *
     * @param  Request $request
     * @return Response
     */
    public function search(Request $request)
    {
        $op = DbHelper::isPostgres() ? 'ILIKE' : 'LIKE';
        $q = $request->input('query') . '%';
        $items = Item::where('barcode', $op, $q)
            ->where('library_id', '=', \Auth::user()->id)
            ->limit(10)
            ->get()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'type' => 'item',
                    'name' => $item->barcode,
                    'group' => $item->thing->name,
                ];
            });

        return response()->json($items);
    }

    /**
     * Display the specified resource.
     *
     * @param Item $item
     * @return Response
     */
    public function show(Item $item)
    {
        return response()->view('items.show', array(
            'item' => $item,
            'lastLoan' => $item->loans()->withTrashed()->orderBy('id', 'desc')->first(),
        ));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Item $item
     * @param Request $request
     * @return Response
     */
    public function editForm(Item $item, Request $request)
    {
        if ($request->input('thing')) {
            $item->thing = Thing::find($request->input('thing'));
        }
        return response()->view('items.edit', array(
            'item' => $item,
        ));
    }

    /**
     * Update or create the specified resource in storage.
     *
     * @param Item $item
     * @param Request $request
     * @return Response
     */
    public function upsert(Item $item, Request $request)
    {
        \Validator::make($request->all(), [
            'barcode' => 'required|unique:items,barcode' . ($item->barcode ? ',' . $item->id : ''),
            'thing' => 'exists:things,id',
        ], $this->messages)->validate();

        $item->barcode = $request->input('barcode');
        $item->note = $request->input('note');
        $item->thing_id = intval($request->input('thing'));

        $item->save();

        return redirect()->action('ThingsController@show', $item->thing->id)
            ->with('status', 'Eksemplaret ' . $item->barcode . ' ble lagret!');
    }

    /**
     * Show the form for deleting the specified resource.
     *
     * @param Item $item
     * @param Request $request
     * @return Response
     */
    public function deleteForm(Item $item, Request $request)
    {
        if ($item->loans()->count()) {
            return redirect()->action('ItemsController@show', $item->id)
                ->with('error', 'Kan ikke slette utlånt eksemplar.');
        }

        return response()->view('items.delete', array(
            'item' => $item,
        ));
    }

    /**
     * Delte the specified resource from storage.
     *
     * @param Item $item
     * @return Response
     */
    public function delete(Item $item)
    {
        if ($item->loans()->count()) {
            return redirect()->action('ItemsController@show', $item->id)
                ->with('error', 'Kan ikke slette utlånt eksemplar.');
        }

        \Log::info(sprintf(
            'Slettet %s <a href="%s">%s</a>.',
            $item->thing->properties->get('name_definite.nob'),
            action('ItemsController@show', $item->id),
            $item->barcode
        ));
        $item->delete();

        return redirect()->action('ItemsController@show', $item->id)
            ->with('status', 'Eksemplaret ble slettet!');
    }

    /**
     * Restore the specified resource.
     *
     * @param Item $item
     * @return Response
     */
    public function restore(Item $item)
    {
        $item->found();

        \Log::info(sprintf(
            'Gjenopprettet %s <a href="%s">%s</a>.',
            $item->thing->properties->get('name_definite.nob'),
            action('ItemsController@show', $item->id),
            $item->barcode
        ));

        return redirect()->action('ItemsController@show', $item->id)
            ->with('status', 'Eksemplaret ble gjenopprettet.');
    }
}
