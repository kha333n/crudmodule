<?php

return [
    /**
     * Enable or disable the API routes
     *
     * Disable this and use your own controllers to interact with the Repository for greater control
     */
    'enable_api' => true,

    /**
     * The prefix for the API routes
     *
     * By default, it is null, so the routes will be at the root level
     */
    'route_prefix' => null,

    /**
     * The GLOBAL middleware for the API routes.
     * Specific to each route authorization will be handled in Model.
     *
     * By default, web as mostly users will be using this package in web routes
     *
     * Change it or add your own middleware as required
     */
    'middleware' => ['web'],

    /**
     * Map the route name to the model class
     *
     * This is useful when you want to use a different model class for a route
     * OR model is not in default directory 'App\Models'
     *
     * @example 'books' => 'App\Models\Library\Book'
     */
    'model_route_mappings' => [
        // 'route-name' => 'App\Custom\Namespace\ModelClass',
    ],
];
