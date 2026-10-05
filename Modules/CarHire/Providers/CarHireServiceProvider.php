<?php

namespace Modules\CarHire\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class CarHireServiceProvider extends ServiceProvider
{
    /**
     * Boot the module services.
     */
    public function boot()
    {
        $this->registerRoutes();
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'carhire');
    }

    /**
     * Register the module services.
     */
    public function register()
    {
        //
    }

    /**
     * Register module routes.
     */
    protected function registerRoutes()
    {
        // Web routes
        if (file_exists(__DIR__ . '/../Routes/web.php')) {
            Route::middleware('web')
                ->group(__DIR__ . '/../Routes/web.php');
        }

        // API routes
        if (file_exists(__DIR__ . '/../Routes/api.php')) {
            Route::prefix('api')
                ->middleware('api')
                ->group(__DIR__ . '/../Routes/api.php');
        }
    }
}
