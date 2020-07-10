<?php namespace App\Logger;

use App\Logger\LogProviderInterface;

class LogToFile implements LogProviderInterface
{
    public function log($log_message)
    {
        \Log::info($log_message);
    }
}
