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
	public function getIndex()
	{
		$items = Item::with('loans', 'thing')->whereNotNull('dokid')->get();

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
            ->limit(10)
            ->get()->map(function($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->dokid,
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
	public function getShow(Item $item)
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
    public function getEdit(Item $item, Request $request)
    {
        if ($request->input('thing')) {
            $item->thing = Thing::find($request->input('thing'));
        }
        return response()->view('items.edit', array(
            'item' => $item,
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Item $item
     * @param Request $request
     * @return Response
     */
    public function postUpdate(Item $item, Request $request)
    {
        \Validator::make($request->all(), [
            'dokid' => 'required|unique:items,dokid' . ($item->dokid ? ',' . $item->id : ''),
            'thing' => 'exists:things,id',
        ], $this->messages)->validate();

        $item->dokid = $request->input('dokid');
        $item->note = $request->input('note');
        $item->thing_id = intval($request->input('thing'));

        $item->save();

        return redirect()->action('ItemsController@getShow', $item->id)
            ->with('status', 'Eksemplaret ble lagret!');
    }

    /**
     * Show the form for deleting the specified resource.
     *
     * @param Item $item
     * @param Request $request
     * @return Response
     */
    public function getDelete(Item $item, Request $request)
    {
        return response()->view('items.delete', array(
            'item' => $item,
        ));
    }

    /**
     * Delte the specified resource from storage.
     *
     * @param Item $item
     * @param Request $request
     * @return Response
     */
    public function postDelete(Item $item, Request $request)
    {
        $item->delete();

        return redirect()->action('ItemsController@getShow', $item->id)
            ->with('status', 'Eksemplaret ble slettet!');
    }

    /**
     * Restore the specified resource.
     *
     * @param Item $item
     * @return Response
     */
    public function getRestore(Item $item)
    {
        $item->restore();

        return redirect()->action('ItemsController@getShow', $item->id)
            ->with('status', 'Eksemplaret ble gjenopprettet.');
    }

}
