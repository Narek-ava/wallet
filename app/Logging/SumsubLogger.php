<?php

namespace App\Logging;

use Monolog\Logger;

class SumsubLogger
{
    const LOGGER_NAME = 'SumsubLoggingHandler';

    const CHANNEL = 'sumsub';
    /**
     * Create a custom Monolog instance.
     * @param  array  $config
     * @return \Monolog\Logger
     */
    public function __invoke(array $config)
    {
        $logger = new Logger(self::LOGGER_NAME);
        return $logger->pushHandler(new SumsubLoggingHandler());
    }
}
