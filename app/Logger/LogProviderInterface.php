<?php namespace App\Logger;

interface LogProviderInterface
{
    public function log($attr);
}
