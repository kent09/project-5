<?php

namespace App\Console\Commands;

use App\Services\InfusionsoftSyncServiceV2;
use Illuminate\Console\Command;

class SyncFailed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:failed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check errored imports';

    protected $service;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(InfusionsoftSyncServiceV2 $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        return $this->service->syncFailed();
    }
}
