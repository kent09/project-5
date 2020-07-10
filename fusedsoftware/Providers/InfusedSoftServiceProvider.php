<?php

namespace FusedSoftware\Providers;

use Illuminate\Support\ServiceProvider;
use FusedSoftware\Contracts\InfusionSoftContract;
use FusedSoftware\Services\InfusionSoft;

class InfusedSoftServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->bindInfusionSoftContract();
    }

    /**
     * Register infusionsoft
     *
     * @return void
     */
    private function bindInfusionSoftContract()
    {
        $this->app->bind(InfusionSoftContract::class, function () {
            return new InfusionSoft;
        });
    }

}
