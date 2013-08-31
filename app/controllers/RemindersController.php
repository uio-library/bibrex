<?php

class RemindersController extends BaseController {

	/**
	 * Display a form to create the resource.
	 *
	 * @return Response
	 */
	public function getCreate()
	{
		$loan_id = Input::get('loan_id');
		$loan = Loan::find($loan_id);
		if (!$loan) {
			die('Oh noes, no (valid) loan specified!');
		}
		return Response::view('reminders.create', array(
			'reminder' => new Reminder(),
			'loan' => $loan
		));
	}

	/**
	 * Stores a new reminder.
	 *
	 * @return Response
	 */
	public function postStore()
	{
		die('not implemented yet');
	}

}