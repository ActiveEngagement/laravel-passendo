<?php

namespace Actengage\LaravelPassendo;

use Illuminate\Support\ServiceProvider;

class PassendoServiceProvider extends ServiceProvider {

    /**
     * Register the service.
     * 
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/passendo.php', 'passendo');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations')
        ], 'migrations');
    }

    /**
     * Boot the services.
     * 
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}