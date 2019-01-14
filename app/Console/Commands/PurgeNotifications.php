<?php

namespace App\Console\Commands;

use App\Notifications\ExtendedDatabaseNotification;
use Carbon\Carbon;

class PurgeNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bibrex:purge-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge old notifications.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $storageTime = (int) config('bibrex.storage_time.notifications');
        if ($storageTime <= 0) {
            $this->logError('The `bibrex.storage_time.notifications` config value is invalid.');
            return;
        }

        $n = 0;
        $m = 0;

        // Check imported users
        $notifications = ExtendedDatabaseNotification::where('created_at', '<', Carbon::now()->subDays($storageTime))
            ->get();

        foreach ($notifications as $notification) {
            if ($notification->loan->trashed()) {
                $notification->delete();
                $m++;
            }
            $n++;
        }

        $this->logInfo("Sletting av gamle meldinger: $m av $n meldinger eldre enn $storageTime dager ble slettet.");
    }
}
