<?php

namespace App;

use App\Mail\FirstReminder;
use Illuminate\Database\Eloquent\Model;

class Reminder extends Model {

	protected $templates = [
		'eng' => 'emails.reminders.eng_html',
		'nob' => 'emails.reminders.nob_html',
        'nno' => 'emails.reminders.nob_html',  // @TODO
	];

	protected $guarded = array();

	public static $rules = array();

	public static function fromLoan($loan) {
		$messages = [
			'nob' => [
				'thingMustBeReturned' => '{thing} må leveres tilbake',
			],
            'nno' => [
                'thingMustBeReturned' => '{thing} må leveres tilbake',  // @TODO
            ],
			'eng' => [
				'thingMustBeReturned' => '{thing} must be returned',
			],
		];
		$thing = $loan->item->thing;

		if ($loan->user->lang == 'eng') {
			$subject = mb_ucfirst(str_replace('{thing}', $thing->email_name_definite_eng,
				array_get($messages, 'eng.thingMustBeReturned')));
			$view = 'emails.reminders.eng_html';
			$view_args = [
				'indefinite' => $thing->email_name_eng,
				'definite' => $thing->email_name_definite_eng,
				'relativeTime' => $loan->relativeCreationTime(),
                'library' => \Auth::user()->name_eng,
			];
		} else {
			$subject = mb_ucfirst(str_replace('{thing}', $thing->email_name_definite_nob,
				array_get($messages, 'nob.thingMustBeReturned')));
			$view = 'emails.reminders.nob_html';
			$view_args = [
				'indefinite' => $thing->email_name_nob,
				'definite' => $thing->email_name_definite_nob,
				'relativeTime' => $loan->relativeCreationTime(),
                'library' => \Auth::user()->name,
			];
		}

		$reminder = new Reminder();
		$reminder->loan_id = $loan->id;
		$reminder->subject = $subject;
		$reminder->body = \View::make($view, $view_args);

		return $reminder;
	}

	public function loan()
	{
		return $this->belongsTo(Loan::class);
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
			\Mail::send(new FirstReminder($this));

			\Log::info('Sendte <a href="'. \URL::action('RemindersController@getShow', $this->id) . '">påminnelse</a> for lån.');
		}

		return true;
	}
}
