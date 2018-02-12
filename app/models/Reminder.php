<?php

class Reminder extends Eloquent {

	protected $templates = [
		'eng' => 'emails.reminders.eng_html',
		'nor' => 'emails.reminders.nor_html',
	];

	protected $guarded = array();

	public static $rules = array();

	public static function fromLoan($loan) {
		$messages = [
			'nor' => [
				'thingMustBeReturned' => '{thing} må leveres tilbake',
			],
			'eng' => [
				'thingMustBeReturned' => '{thing} must be returned',
			],
		];
		$thing = $loan->document->thing;

		if ($loan->user->lang == 'eng') {
			$subject = mb_ucfirst(str_replace('{thing}', $thing->email_name_definite_eng,
				array_get($messages, 'eng.thingMustBeReturned')));
			$view = 'emails.reminders.eng_html';
			$view_args = [
				'indefinite' => $thing->email_name_eng,
				'definite' => $thing->email_name_definite_eng,
				'relativeTime' => $loan->relativeCreationTime(),
			];
		} else {
			$subject = mb_ucfirst(str_replace('{thing}', $thing->email_name_definite_nor,
				array_get($messages, 'nor.thingMustBeReturned')));
			$view = 'emails.reminders.nor_html';
			$view_args = [
				'indefinite' => $thing->email_name_nor,
				'definite' => $thing->email_name_definite_nor,
				'relativeTime' => $loan->relativeCreationTime(),
			];
		}

		$reminder = new Reminder();
		$reminder->loan_id = $loan->id;
		$reminder->subject = $subject;
		$reminder->body = View::make($view, $view_args);

		return $reminder;
	}

	public function loan()
	{
		return $this->belongsTo('Loan');
	}

	public function sendEmail()
	{
		Mail::send(
			['text' => 'emails.generic'],
			['content' => $this->body],
			function($message) {
				$user = $this->loan->user;
				$name = $user->firstname . ' ' . $user->lastname;
				$email = $user->email;
				$message->to($email, $name)->subject($this->subject);
			}
		);
	}

	/**
	 * Save the model to the database.
	 *
	 * @param  array  $options
	 * @return bool
	 */
	public function save(array $options = array())
	{
		$isNew = !$this->exists;

		parent::save($options);

		if ($isNew) {
			$this->sendEmail();

			Log::info('Sendte <a href="'. URL::action('RemindersController@getShow', $this->id) . '">påminnelse</a> for lån.');
		}

		return true;
	}
}
