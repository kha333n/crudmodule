<?php

namespace kha333n\crudmodule\Providers;

use Illuminate\Support\ServiceProvider;

class CrudServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Publish the config file
        $this->publishes([
            __DIR__ . '/../config/crudmodule.php' => config_path('crudmodule.php'),
        ], 'config');

        // Publish the routes file
        $this->publishes([
            __DIR__ . '/../routes/api.php' => base_path('routes/kha333n/crudmodule/api.php'),
        ], 'routes');

        $this->mergeConfigFrom(__DIR__ . '/../config/crudmodule.php', 'crudmodule');

        if (config('crudmodule.enable_api')) {
            // Load the routes file conditionally
            if (file_exists(base_path('routes/kha333n/crudmodule/api.php'))) {
                $this->loadRoutesFrom(base_path('routes/kha333n/crudmodule/api.php'));
            } else {
                $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
            }
        }
    }

    public function boot()
    {
        // Register package events or other bootstrapping here if necessary
    }
}
