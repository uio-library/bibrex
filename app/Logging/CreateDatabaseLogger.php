<?php

namespace App\Logging;

use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;

class CreateDatabaseLogger
{
    /**
     * Create a custom Monolog instance.
     *
     * @param  array  $config
     * @return \Monolog\Logger
     */
    public function __invoke(array $config)
    {
        $logger = new Logger('bibrex');
        $logger->pushProcessor(new IntrospectionProcessor());
        $conn = \DB::connection();
        $logger->pushHandler(new DatabaseLoggingHandler($conn));

        return $logger;
    }
}
