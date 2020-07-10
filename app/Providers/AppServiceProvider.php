<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->changeDomainConfig();
        $this->getSubdomain();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Cashier::ignoreMigrations();
    }

    /**
     * Change the session domain
     * 
     * @return void
     */
    private function changeDomainConfig()
    {
        config([
            'session.domain' => '.'.env('APP_URL')
        ]);
    }

    /**
     * Get the subdomain 
     * 
     * @return void
     */
    private function getSubdomain()
    {
        $host = '';
        if (isset($_SERVER['HTTP_HOST'])) {
            $host = explode('.', $_SERVER['HTTP_HOST'])[0];
        }
        
        config(['fusedsoftware.subdomain' => $host]);
    }
}
