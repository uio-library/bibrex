<?php

namespace App\Console\Commands;

use App\Events\NewVersionDeployed;
use Illuminate\Console\Command;

class SendNewVersionNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bibrex:version-notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify clients about a new deploy.';

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
        event(new NewVersionDeployed());
    }
}
