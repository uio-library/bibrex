<?php

class DocumentsController extends BaseController {

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
		$docs = Document::with('loans', 'thing')->get();
		$this->layout->content = View::make('documents.index')
			->with('documents', $docs);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function getShow($id)
	{
		View::composer('layouts.master', function($view){
			$view->with('status', Session::get('status'));
		});

		$document = Document::with('thing')->find($id);
		return View::make('documents.show')
			->with('document', $document);
	}

}