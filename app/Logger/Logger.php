<?php namespace App\Logger;

use App\Logger\LogProviderInterface;

class Logger
{
    protected $logger;

    public function __construct(LogProviderInterface $logger)
    {
        $this->logger = $logger;
    }

    public function writeDown($log_message)
    {
        $this->logger->log($log_message);
    }
}
