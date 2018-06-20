<?php

namespace App\Console\Commands;

use App\Loan;
use App\Mail\FirstReminder;
use App\Reminder;
use Carbon\Carbon;
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

        $n = 0;
        foreach (Loan::with('item','user','library', 'reminders')->get() as $loan) {
            if ($loan->item->thing->send_reminders) {

                if ($loan->due_at->getTimestamp > Carbon::now()->getTimestamp()) {
                    $this->comment('Loan ' . $loan->user->id . ' is not due yet.');
                    continue;
                }

                if (count($loan->reminders) > 0) {
                    $this->comment('Reminder already sent for ' . $loan->user->id);
                    continue;
                }

                // Send first reminder
                if (empty($loan->user->email)) {
                    $this->error('Cannot send reminder. No email set for user ' . $loan->user->id);
                    \Log::error('Cannot send reminder. No email set for user ' . $loan->user);
                } else {
                    $this->info('Sending reminder to ' . $loan->user->email);

                    \Mail::send((new FirstReminder($loan))->save());
                    \Log::info('Sendte <a href="'. \URL::action('RemindersController@getShow', $reminder->id) . '">påminnelse</a> for lån.');

                    $n++;
                }
            }
        }
        \Log::info('Sent ' . $n . ' reminders.');

        $this->info(sprintf('-[ %s : Send reminders complete ]------------------------------------', strftime('%Y-%m-%d %H:%M:%S')));
    }
}
