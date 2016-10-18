<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class Anonymize extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'anonymize';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Anonymize returned loans.';

	protected $anon_user_id = 1641;

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
		foreach (Loan::withTrashed()->with('user')->get() as $loan) {
			if (!is_null($loan->deleted_at)) {
				$loan->user_id = $this->anon_user_id;
				$loan->save();
			}
		}
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
