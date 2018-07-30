<?php

namespace App\Console;

use App\Console\Commands\Anonymize;
use App\Console\Commands\DeleteInactiveUsers;
use App\Console\Commands\PurgeLogs;
use App\Console\Commands\SendReminders;
use App\Console\Commands\SyncUsers;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command(SendReminders::class)->dailyAt('08:00');
        $schedule->command(DeleteInactiveUsers::class)->monthly();

        $schedule->command(Anonymize::class)
            ->dailyAt('04:00')
            ->then(function () {
                $this->call(PurgeLogs::class);
                $this->call(SyncUsers::class);
            });
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
