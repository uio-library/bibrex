<?php

class RemindersController extends BaseController
{

	/**
	 * Show a reminder.
	 *
	 * @return Response
	 */
	public function getShow($id)
	{
		$reminder = Reminder::find($id);
		if (!$reminder) {
			die('Oh noes, not found!');
		}

		return Response::view('reminders.show', array(
			'from' => Config::get('mail.from.address'),
			'reminder' => $reminder,
			'loan' => $reminder->loan,
		));
	}

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

		$reminder = Reminder::fromLoan($loan);

		return Response::view('reminders.create', array(
			'reminder' => $reminder,
			'loan' => $loan,
			'subject' => $reminder->subject,
			'body' => preg_replace('/\n/', '<br>', $reminder->body),
		));
	}

	/**
	 * Stores a new reminder.
	 *
	 * @return Response
	 */
	public function postStore()
	{
		$loan_id = intval(Input::get('loan_id'));
		$loan = Loan::find($loan_id);

		$reminder = Reminder::fromLoan($loan);
		$reminder->save();

		return Redirect::action('LoansController@getShow', $loan->id)
				->with('status', 'Påminnelse sendt.');
	}
}