<?php

namespace App\Console\Commands;

use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DeleteInactiveUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bibrex:purge-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge inactive users.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected function logInfo($msg)
    {
        $this->info($msg);
        \Log::info($msg);
    }

    protected function logError($msg)
    {
        $this->error($msg);
        \Log::error($msg);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $daysLocal = (int) config('bibrex.user_storage_time.local');
        if ($daysLocal <= 0) {
            $this->logError('The `bibrex.user_storage_time.local` config value is invalid.');
            return;
        }

        $daysImported = (int) config('bibrex.user_storage_time.imported');
        if ($daysImported <= 0) {
            $this->logError('The `bibrex.user_storage_time.imported` config value is invalid.');
            return;
        }

        $n = 0;

        // Check imported users
        $users = User::doesntHave('loans')
            ->where('last_loan_at', '<', Carbon::now()->subDays($daysImported))
            ->where('in_alma', '=', 1)
            ->get();

        foreach ($users as $user) {
            $this->logInfo(
                "Sletting av inaktive brukere: Slettet {$user->name} ({$user->id})," .
                " sist aktiv {$user->last_loan_at->toDateString()}."
            );
            $user->delete();
            $n++;
        }

        // Check local users
        $users = User::doesntHave('loans')
            ->where('last_loan_at', '<', Carbon::now()->subDays($daysLocal))
            ->where('in_alma', '=', 0)
            ->get();

        foreach ($users as $user) {
            $this->logInfo(
                "Sletting av inaktive brukere: Slettet {$user->name} ({$user->id})," .
                " sist aktiv {$user->last_loan_at->toDateString()}."
            );
            $user->delete();
            $n++;
        }

        $this->info("Slettet $n brukere.");
    }
}
