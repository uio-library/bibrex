<?php

class ThingsController extends BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getIndex()
	{
		$things = Thing::with('documents.loans')->get();
		return Response::view('things.index', array(
				'things' => $things
			));

	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function postStore()
	{

		$validator = Validator::make(Input::all(), array(
			'thing' => array('required')
		), array(
			'thing.required' => 'ting må fylles ut',
		));
		if ($validator->fails())
		{
			return Redirect::action('ThingsController@getIndex')
				->withErrors($validator)
				->withInput();
		}

		$thing = Thing::where('name','=',Input::get('thing'))->first();
		if ($thing) {
			return Redirect::action('ThingsController@getIndex')
				->with('status', 'Tingen finnes allerede!');
		}

		$thing = new Thing();
		$thing->name = Input::get('thing');
		$thing->save();
			return Redirect::action('ThingsController@getIndex')
				->with('status', 'Tingen ble lagret!');
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  string  $id
	 * @return Response
	 */
	public function getShow($id)
	{

		$thing = Thing::find($id);
		if (!$thing) {
			return Response::view('errors.missing', array('what' => 'Tingen'), 404);
		}
		return Response::view('things.show', array(
			'thing' => $thing
		));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function getEdit($id)
	{
		$thing = Thing::find($id);
		return Response::view('things.edit', array(
				'thing' => $thing
			));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function postUpdate($id)
	{

		$validator = Validator::make(Input::all(), array(
			'name' => array('required')
		), array(
			'name.required' => 'Navnet kan ikke være blankt',
		));
		if ($validator->fails())
		{
			return Redirect::action('ThingsController@getEdit', $id)
				->withErrors($validator)
				->withInput();
		}

		$thing = Thing::find($id);
		if (!$thing) {
			return Redirect::action('ThingsController@getIndex')
				->with('status', 'Tingen finnes ikke!');
		}

		$thing->name = Input::get('name');
		$thing->save();
			return Redirect::action('ThingsController@getIndex')
				->with('status', 'Tingen ble lagret!');
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function getDestroy($id)
	{
		//
	}

}