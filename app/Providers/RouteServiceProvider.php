<?php

namespace App\Providers;

// use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    // public function boot(Router $router)
    public function boot()
    {
        //
        parent::boot();
        // parent::boot($router);
    }

    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function map()
    {
        $this->mapWebRoutes();

        $this->mapApiRoutes();

        $this->mapToolsRoutes();

        $this->mapDocsRoutes();

        $this->mapInvoiceRoutes();
        //
        $this->mapBillingRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::group([
            'namespace' => $this->namespace, 'middleware' => 'web',
        ], function ($router) {
            require base_path('routes/web.php');
        });
    }


    protected function mapToolsRoutes() {
        Route::group([
            'namespace' => $this->namespace, 'middleware' => 'web',
        ], function ($router) {
            require base_path('routes/tools.php');
        });
    }

    protected function mapDocsRoutes() {
        Route::group([
            'namespace' => $this->namespace, 'middleware' => 'web',
        ], function ($router) {
            require base_path('routes/docs.php');
        });
    }

    protected function mapInvoiceRoutes() {
        Route::group([
            'namespace' => $this->namespace, 'middleware' => 'web',
        ], function ($router) {
            require base_path('routes/invoice.php');
        });
    }

    protected function mapBillingRoutes() {
        Route::group([
            'namespace' => $this->namespace, 'middleware' => 'web',
        ], function ($router) {
            require base_path('routes/billing.php');
        });
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::group([
            'middleware' => 'api',
            'namespace' => $this->namespace,
            'prefix' => 'api',
        ], function ($router) {
            require base_path('routes/api.php');
        });
    }
}
