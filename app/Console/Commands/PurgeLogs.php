<?php

namespace App\Console\Commands;

class PurgeLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bibrex:purge-logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge old log entries.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $channels = config('logging.channels.stack.channels');
        if (!in_array('postgres', $channels)) {
            return;
        }

        $days = (int) config('logging.channels.postgres.days');
        if ($days <= 0) {
            return;
        }

        $deleted = \DB::delete("DELETE FROM log WHERE time < now() - interval '$days day'");
        if ($deleted > 0) {
            $this->logInfo("$deleted loggmeldinger ble slettet");
        } else {
            $this->info("Ingen loggmeldinger ble slettet");
        }
    }
}
