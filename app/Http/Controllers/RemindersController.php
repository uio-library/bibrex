<?php

namespace App\Http\Controllers;

use App\Loan;
use App\Mail\FirstReminder;
use App\Reminder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RemindersController extends Controller
{

    /**
     * Show a reminder.
     *
     * @param Reminder $reminder
     * @return Response
     */
	public function getShow(Reminder $reminder)
	{
		return response()->view('reminders.show', array(
			'reminder' => $reminder,
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

		$reminder = (new FirstReminder($loan))->getReminder();

		return response()->view('reminders.create', array(
			'reminder' => $reminder,
			'loan' => $loan,
			'subject' => $reminder->subject,
			'body' => preg_replace('/\n/', '<br>', $reminder->body),
		));
	}

    /**
     * Sends a new reminder.
     *
     * @param Request $request
     * @return Response
     */
	public function postStore(Request $request)
	{
		$loan_id = intval($request->input('loan_id'));
		$loan = Loan::find($loan_id);


        \Mail::send((new FirstReminder($loan))->save());
        \Log::info('Sendte påminnelse for <a href="'. \URL::action('LoansController@getShow', $loan->id) . '">lån</a>.');

		return redirect()->action('LoansController@getShow', $loan->id)
				->with('status', 'Påminnelsen ble sendt.');
	}
}
