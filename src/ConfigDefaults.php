<?php

return [

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
        'connection' => 'sqlite_mem',

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
        'connection' => 'sqlite_mem',

        // connection options
        'options'    => [
            'driver'      => 'database',
            'table'       => 'jobs',
            'queue'       => 'default',
            'retry_after' => 90,
        ],
    ],

];