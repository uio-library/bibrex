<?php

class DocumentsController extends BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getIndex()
	{
		$docs = Document::with('loans', 'thing')->get();
		return Response::view('documents.index', array(
			'documents' => $docs
		));
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function getShow($id)
	{

		$document = Document::with('thing')->find($id);
		if (!$document) {
		    return Response::view('errors.missing', array('what' => 'Dokumentet'), 404);
		}
		return Response::view('documents.show', array(
			'document' => $document
		));
	}

}