<?php

class ThingsController extends BaseController {

	/**
	 * The layout that should be used for responses.
	 */
	protected $layout = 'layouts.master';

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getIndex()
	{
		$things = Thing::all();
		$this->layout->content = View::make('things.index')
			->with('things', $things);
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
	 * @param  int  $id
	 * @return Response
	 */
	public function getShow($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function getEdit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function postUpdate($id)
	{
		//
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