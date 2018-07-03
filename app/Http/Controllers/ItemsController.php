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
        'dokid.required' => 'Strekkode må legges inn.',
        'dokid.unique' => 'Strekkoden er allerede i bruk. Du må legge inn en unik strekkode.',
    ];

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$items = Item::with('loans', 'thing')
            ->whereNotNull('dokid')
            ->where('library_id', '=', \Auth::user()->id)
            ->get();

		return response()->view('items.index', array(
			'items' => $items
		));
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
        $items = Item::where('dokid', $op, $q)
            ->where('library_id', '=', \Auth::user()->id)
            ->limit(10)
            ->get()->map(function($item) {
                return [
                    'id' => $item->id,
                    'type' => 'item',
                    'name' => $item->dokid,
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
            'dokid' => 'required|unique:items,dokid' . ($item->dokid ? ',' . $item->id : ''),
            'thing' => 'exists:things,id',
        ], $this->messages)->validate();

        $item->dokid = $request->input('dokid');
        $item->note = $request->input('note');
        $item->thing_id = intval($request->input('thing'));

        $item->save();

        return redirect()->action('ThingsController@getShow', $item->thing->id)
            ->with('status', 'Eksemplaret ' . $item->dokid . ' ble lagret!');
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

        \Log::info(sprintf('Slettet %s <a href="%s">%s</a>.',
            $item->thing->properties->name_definite->nob,
            action('ItemsController@show', $item->id),
            $item->dokid));
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
        $item->restore();
        \Log::info(sprintf('Gjenopprettet %s <a href="%s">%s</a>.',
            $item->thing->properties->name_definite->nob,
            action('ItemsController@show', $item->id),
            $item->dokid));

        return redirect()->action('ItemsController@show', $item->id)
            ->with('status', 'Eksemplaret ble gjenopprettet.');
    }

}
