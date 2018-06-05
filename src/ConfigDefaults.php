<?php

return [

    /*
     |--------------------------------------------------------------------------
     | Components to load and order
     |--------------------------------------------------------------------------
     |
     | A component is a handler for a specific feature or setting that needs to
     | be run in the setup of Application and Kernel. Very similar to service
     | providers in Laravel, components handle the setup and boot setting,
     |
     */

    'components' => [
        \WebChefs\LaraAppSpawn\Components\Database::class,
        \WebChefs\LaraAppSpawn\Components\Queue::class,
    ],

    /*
     |--------------------------------------------------------------------------
     | Callbacks
     |--------------------------------------------------------------------------
     |
     | Allow for dynamic process of configs in spawned apps
     |
     */

    'callback' => [

        // When App is spawned from vendor, inject custom service providers
        // for travis-ci builds here.
        'vendor_config' => function(array $config) {
            return $config;
        },

    ],

    /*
     |--------------------------------------------------------------------------
     | Default database connection
     |--------------------------------------------------------------------------
     |
     | database.connections[connection] = options
     | database.default = connection
     |
     */

    'database' => [

        // If null resolved relative to caller
        'path'       => null,

        // connection name
        'connection' => 'spawn_sqlite_mem',

        // connection options
        'options'    => [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ],
    ],

    /*
     |--------------------------------------------------------------------------
     | Default queue drivers
     |--------------------------------------------------------------------------
     |
     | queue.connections[connection] = options
     | queue.default = connection
     |
     */

    'queue' => [
        // connection name
        'connection' => 'spawn_sqlite_mem',

        // connection options
        'options'    => [
            'driver'      => 'database',
            'table'       => 'jobs',
            'queue'       => 'default',
            'retry_after' => 90,
        ],
    ],

];