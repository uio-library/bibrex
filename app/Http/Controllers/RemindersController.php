<?php

namespace App\Http\Controllers;

use App\Loan;
use App\Reminder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RemindersController extends Controller
{

    /**
     * Show a reminder.
     *
     * @param $id
     * @return Response
     */
	public function getShow($id)
	{
		$reminder = Reminder::find($id);
		if (!$reminder) {
			die('Oh noes, not found!');
		}

		return response()->view('reminders.show', array(
			'from' => config('mail.from.address'),
			'reminder' => $reminder,
			'loan' => $reminder->loan,
		));
	}

    /**
     * Display a form to create the resource.
     *
     * @param Request $request
     * @return Response
     */
	public function getCreate(Request $request)
	{
		$loan_id = $request->input('loan_id');
		$loan = Loan::with('user', 'item.thing')->find($loan_id);
		if (!$loan) {
			die('Oh noes, no (valid) loan specified!');
		}

		$reminder = Reminder::fromLoan($loan);

		return response()->view('reminders.create', array(
			'reminder' => $reminder,
			'loan' => $loan,
			'subject' => $reminder->subject,
			'body' => preg_replace('/\n/', '<br>', $reminder->body),
		));
	}

    /**
     * Stores a new reminder.
     *
     * @param Request $request
     * @return Response
     */
	public function postStore(Request $request)
	{
		$loan_id = intval($request->input('loan_id'));
		$loan = Loan::find($loan_id);

		$reminder = Reminder::fromLoan($loan);
		$reminder->save();

		return redirect()->action('LoansController@getShow', $loan->id)
				->with('status', 'Påminnelse sendt.');
	}
}
