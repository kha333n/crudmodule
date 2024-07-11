<?php

namespace Providers;

use Illuminate\Support\ServiceProvider;
use Repositories\CrudRepository;

class CrudServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind('kha333n\crudmodule\Repositories\CrudRepository', function ($app, $params) {
            return new CrudRepository($params['model']);
        });
    }

    public function boot()
    {
        // Register package events or other bootstrapping here if necessary
    }
}
