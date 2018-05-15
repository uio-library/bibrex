<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class SendReminders extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'send:reminders';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Send reminders.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function fire()
	{
        $this->info(sprintf('-[ %s : Send reminders start ]------------------------------------', strftime('%Y-%m-%d %H:%M:%S')));


		foreach (Loan::with('document','user','library', 'reminders')->get() as $loan) {
			if ($loan->document->thing->send_reminders) {

				// First reminder
				if (count($loan->reminders) == 0) {
					if (empty($loan->user->email)) {
						$this->error('Cannot send reminder. No email set for user ' . $loan->user->id);
					} else {
						$this->info('Sending reminder to ' . $loan->user->email);
						$reminder = Reminder::fromLoan($loan);
						$reminder->save();
					}

				} else {
					$this->comment('Reminder already sent for ' . $loan->user->id);
				}

			}
		}

        $this->info(sprintf('-[ %s : Send reminders complete ]------------------------------------', strftime('%Y-%m-%d %H:%M:%S')));
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(

		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(

		);
	}

}
