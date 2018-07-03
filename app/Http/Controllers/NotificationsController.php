<?php

namespace App\Http\Controllers;

use App\Loan;
use App\Notifications\ExtendedDatabaseNotification;
use App\Notifications\FirstReminder;
use App\Notifications\ManualReminder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NotificationsController extends Controller
{

    /**
     * Show a reminder.
     *
     * @param ExtendedDatabaseNotification $notification
     * @return Response
     */
	public function show(ExtendedDatabaseNotification $notification)
	{
		return response()->view('notifications.show', [
		    'type' => $notification->humanReadableType(),
		    'loan' => $notification->loan,
            'email' => array_get($notification->data, 'email'),
            'sent' => $notification->created_at,
        ]);
	}

    /**
     * Display a form to create the resource.
     *
     * @param Loan $loan
     * @return Response
     */
	public function create(Loan $loan)
	{
		$notification = new FirstReminder($loan);
		$email = $notification->email->toArray();

		return response()->view('notifications.create', array(
			'loan' => $loan,
			'email' => $email,
		));
	}

    /**
     * Sends a new reminder.
     *
     * @param Loan $loan
     * @param Request $request
     * @return Response
     */
	public function send(Loan $loan, Request $request)
	{
        $loan->user->notify(new ManualReminder($loan, $request->input('subject'), $request->input('body')));

		return redirect()
            ->action('LoansController@getShow', $loan->id)
			->with('status', 'Påminnelsen ble sendt.');
	}
}
