<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class NcipSync extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'ncip:sync';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Sync loan status from Ncip service and transfer loans for imported users.';

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

		$user_loans = array();
		$due = array();

		$this->info('Sjekker om dokumenter har blitt returnert i BIBSYS...');

		$ncip = App::make('NcipClient');

		foreach (Loan::with('document','user','library')->get() as $loan) {
			if ($loan->document->thing_id == 1) {
				$guest_ltid = $loan->library->guest_ltid;
				if (is_null($guest_ltid)) {
					// This would happen only if the guest ltid is removed at some point
					continue;
				}
				$dokid = $loan->document->dokid;
				$ltid = $loan->as_guest ? $guest_ltid : $loan->user->ltid;
				//$loan->as_guest = !$loan->user->in_bibsys;

				if (!isset($user_loans[$ltid])) {
					$response = $ncip->lookupUser($ltid);
					$user_loans[$ltid] = array();
					foreach ($response->loanedItems as $item) {
						$user_loans[$ltid][] = $item['id'];
						$due[$item['id']] = $item['dateDue'];
					}
				}

				if (in_array($dokid, $user_loans[$ltid])) {

					$this->comment($dokid . ' er fortsatt utlånt til ' . $ltid);

					if (is_null($loan->due_at)) {
						Log::info('[Sync] Oppdaterer forfallsdato for [[Document:' . $dokid . ']]');
					}
					$loan->due_at = $due[$dokid];
					$loan->save();
				} else {
					Log::info('[Sync] Dokumentet [[Document:' . $dokid . ']] har blitt returnert i BIBSYS, så vi returnerer det i BIBREX også');

					$this->info($dokid . ' har blitt returnet i BIBSYS');

					$loan->delete(); 	// Kan ha blitt lånt ut til en annen bruker i mellomtiden.
										// Vi bør derfor *ikke* returnere i NCIP
				}

			}
		}

		$this->info('Sjekker om brukere har blitt importert i BIBSYS...');

		// Checking if there are loans that can be transferred
		$ncipUserData = array();
		foreach (User::with('loans.document.thing')->get() as $user) {
			foreach ($user->loans as $loan) {
				if ($loan->as_guest && $loan->document->thing->id == 1) {
					$ltid = $user->ltid;
					if (!empty($ltid)) {

						//$this->info('Checking loan ' . $loan->id . ' for ' . $ltid);

						if (!isset($ncipUserData[$ltid])) {
							$response = $ncip->lookupUser($ltid);
							$ncipUserData[$ltid] = $response;
						}
						if ($ncipUserData[$ltid]->exists) {
							$this->info($ltid . ' har blitt importert i BIBSYS.');

							$t = $loan->transfer();
							if ($t !== true) {
								$this->error($t);
							}
							Log::info('Synkroniserer brukerdata fra NCIP for bruker ' . $user->id);
							$user->mergeFromUserResponse($ncipUserData[$ltid]);
						} else {
							$this->comment($ltid . ' er fortsatt ikke i BIBSYS.');
						}
					}
				}
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