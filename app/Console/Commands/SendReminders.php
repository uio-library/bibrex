<?php

namespace App\Console\Commands;

use App\Loan;
use App\Reminder;
use Illuminate\Console\Command;

class SendReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:reminders';

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
     * @return mixed
     */
    public function handle()
    {
        $this->info(sprintf('-[ %s : Send reminders start ]------------------------------------', strftime('%Y-%m-%d %H:%M:%S')));


        foreach (Loan::with('item','user','library', 'reminders')->get() as $loan) {
            if ($loan->item->thing->send_reminders) {

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
}
