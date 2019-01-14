<?php

namespace App\Console\Commands;

abstract class Command extends \Illuminate\Console\Command
{
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
}
