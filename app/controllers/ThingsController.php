<?php

class ThingsController extends BaseController {

	protected $thing;

	public function __construct(Thing $thing)
	{
		$this->thingFactory = $thing;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getIndex()
	{
		$library_id = Auth::user()->id;

		$things = $this->thingFactory
			->with('documents.loans')
			->where('library_id', null)
			->orWhere('library_id', $library_id)
			->orderBy('name')
			->get();

		return Response::view('things.index', array(
			'things' => $things
		));
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getAvailableJson($library_id)
	{
		$things = $this->thingFactory
			->with('documents.loans')
			->where('library_id', null)
			->orWhere('library_id', $library_id)
			->get();

		$out = [];
		foreach ($things as $t) {
			$out[] = [
				'name' => $t->name,
				'disabled' => $t->disabled,
				'num_items' => $t->num_items,
				'available_items' => $t->availableItems(),
			];
		}

		return Response::JSON($out);
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getAvailable($library_id)
	{
		return Response::view('things.available', [
			'library_id' => $library_id,
		]);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function postStore()
	{
		$thing = new $this->thingFactory();
		$thing->name = Input::get('thing');

		if (!$thing->save()) {
			return Redirect::action('ThingsController@getIndex')
				->withErrors($thing->errors)
				->withInput();
		}

		return Redirect::action('ThingsController@getEdit', $thing->id)
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
		$thing = $this->thingFactory
			->with('documents.loans')
			->with('documents.allLoans')
			->find($id);
		if (!$thing) {
			return Response::view('errors.missing', array('what' => 'Tingen'), 404);
		}
		return Response::view('things.show', array(
			'thing' => $thing,
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
		if ($id == '_new') {
			return Response::view('things.edit', array(
				'thing' => new Thing(),
				'thing_id' => $id,
			));
		}
		$thing = $this->thingFactory->find($id);
		return Response::view('things.edit', array(
			'thing' => $thing,
			'thing_id' => $id,
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
		if ($id == '_new') {
			$thing = new Thing();
		} else {
			$thing = $this->thingFactory->find($id);
		}
		if (!$thing) {
			return Response::view('errors.missing', array('what' => 'Tingen'), 404);
		}

		$thing->name = Input::get('name');
		$thing->email_name_nor = Input::get('email_name_nor');
		$thing->email_name_eng = Input::get('email_name_eng');
		$thing->email_name_definite_nor = Input::get('email_name_definite_nor');
		$thing->email_name_definite_eng = Input::get('email_name_definite_eng');
		$thing->num_items = Input::get('num_items');
		$thing->disabled = Input::get('disabled') == 'on';
		$thing->send_reminders = Input::get('send_reminders') == 'on';

		if (!$thing->save()) {
			return Redirect::action('ThingsController@getEdit', $id)
				->withErrors($thing->errors)
				->withInput();
		}

		return Redirect::action('ThingsController@getShow', $thing->id)
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
		$thing = $this->thingFactory->find($id);
		if (!$thing) {
			return Response::view('errors.missing', array('what' => 'Tingen'), 404);
		}

		$name = $thing->name;

		if (count($thing->allLoans()) != 0) {
			return Redirect::action('ThingsController@getShow', $thing->id)
				->with('status', 'Beklager, kan bare slette ting som ikke har blitt lånt ut enda.');
		}

		$thing->delete();

		return Redirect::action('ThingsController@getIndex')
			->with('status', 'Tingen «' . $name . '» ble slettet.');
	}

}