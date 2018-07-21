<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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
        $channels = config('logging.channels.stack.channels');
        if (!in_array('postgres', $channels)) {
            return;
        }

        $days = (int) config('logging.channels.postgres.days');
        if ($days <= 0) {
            return;
        }

        $deleted = \DB::delete("DELETE FROM log WHERE time < now() - interval '7 day'");
        $this->info("Purged $deleted log entries");
        if ($deleted > 0) {
            \Log::info("$deleted loggmeldinger ble slettet");
        }
    }
}
