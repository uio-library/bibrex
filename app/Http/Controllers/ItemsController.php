<?php

namespace App\Http\Controllers;

use App\Item;
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
		$items = Item::with('loans', 'thing')->get();

		return response()->view('items.index', array(
			'items' => $items
		));
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
			'item' => $item
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
            'dokid' => 'required|unique:items,dokid' . ($item->dokid ? ',' . $item->dokid : ''),
            'thing' => 'exists:things,id',
        ], $this->messages)->validate();

        $item->dokid = $request->input('dokid');
        $item->thing_id = intval($request->input('thing'));

        $item->save();

        return redirect()->action('ItemsController@getShow', $item->id)
            ->with('status', 'Eksemplaret ble lagret!');
    }
}
