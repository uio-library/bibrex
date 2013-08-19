<?php

class BaseController extends Controller {

	/**
	 * Setup the layout used by the controller.
	 *
	 * @return void
	 */
	protected function setupLayout()
	{
		View::composer('layouts.master', function($view){
			$view->with('status', Session::get('status'));
		});

		if ( ! is_null($this->layout))
		{
			$this->layout = View::make($this->layout)
				->with('status', Session::get('status'));
		}
	}

}