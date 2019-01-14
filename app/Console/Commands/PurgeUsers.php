<?php

namespace App\Console\Commands;

use App\User;
use Carbon\Carbon;

class PurgeUsers extends Command
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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $daysLocal = (int) config('bibrex.storage_time.local_users');
        if ($daysLocal <= 0) {
            $this->logError('The `bibrex.storage_time.local_users` config value is invalid.');
            return;
        }

        $daysImported = (int) config('bibrex.storage_time.imported_users');
        if ($daysImported <= 0) {
            $this->logError('The `bibrex.storage_time.imported_users` config value is invalid.');
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
