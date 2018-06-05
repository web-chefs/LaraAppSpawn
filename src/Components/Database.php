<?php

namespace WebChefs\LaraAppSpawn\Components;

// PHP
use RuntimeException;

// Package
use WebChefs\LaraAppSpawn\Contracts\AppComponentInterface;

// Framework
use Illuminate\Support\Arr;
use Illuminate\Contracts\Config\Repository as ConfigContract;
use Illuminate\Contracts\Foundation\Application;

class Database implements AppComponentInterface
{

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $connection;

    /**
     * @var array
     */
    protected $options;

    public function __construct(array $config)
    {
        $this->validate($config);

        $this->path       = Arr::get($config, 'database.path');
        $this->connection = Arr::get($config, 'database.connection');
        $this->options    = Arr::get($config, 'database.options');
    }

    /**
     * Set test db as default environment

     * @return
     */
    public function setup()
    {
        putenv('DB_DEFAULT=' . $this->connection);
    }

    /**
     * Run setup component.

     * @return
     */
    public function boot(Application $app, ConfigContract &$appConfig)
    {
        // Setup test DB
        $appConfig->set('database.connections.' . $this->connection, $this->options);
        $appConfig->set('database.default', $this->connection);

        // Set database migration path EG: /mysite/database/
        $app->useDatabasePath($this->path);
    }

    /**
     * Validate database config settings exist.
     *
     * @param  array  $config
     *
     * @return void
     * @throws RuntimeException
     */
    protected function validate(array $config)
    {
        if (Arr::has($config, [ 'database.path', 'database.connection', 'database.options' ])) {
            return;
        }

        throw new RuntimeException('Database environment config does not have a valid connection and/or options defined.');
    }

}
