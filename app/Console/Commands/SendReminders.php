<?php

namespace App\Console\Commands;

use App\Loan;
use App\Notifications\FirstReminder;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bibrex:reminders';

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
        foreach (Loan::with('item', 'item.thing', 'item.thing.libraries', 'library', 'user', 'notifications')->get() as $loan) {

            $librarySettings = $loan->item->thing->libraries()
                ->where('library_id', $loan->library->id)
                ->first()
                ->pivot
                ->only('require_item', 'send_reminders');

            if (!array_get($librarySettings, 'send_reminders')) {
                $this->comment("[{$loan->id}] Not sending reminders for {$loan->item->thing->name} from {$loan->library->name}.");
                continue;
            }

            if ($loan->due_at->getTimestamp() > Carbon::now()->getTimestamp()) {
                $this->comment("[{$loan->id}] Loan is not due yet.");
                continue;
            }

            if (count($loan->notifications) > 0) {
                $this->comment("[{$loan->id}] Reminder already sent");
                continue;
            }

            if (empty($loan->user->email)) {
                $this->error("[{$loan->id}] Cannot send reminder. No email set for user {$loan->user->id}");
                \Log::error('Cannot send reminder. No email set for user ' . $loan->user->id);
                continue;
            }

            // Send reminder
            $this->info("[{$loan->id}] Sending reminder to {$loan->user->email}");
            $loan->user->notify(new FirstReminder($loan));
            $n++;
        }
        \Log::info('Sent ' . $n . ' reminders.');

        $this->info(sprintf('-[ %s : Send reminders complete ]------------------------------------', strftime('%Y-%m-%d %H:%M:%S')));
    }
}
