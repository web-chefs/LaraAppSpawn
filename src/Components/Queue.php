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

class Queue implements AppComponentInterface
{

    /**
     * @var string
     */
    protected $connetion;

    /**
     * @var array
     */
    protected $options;

    public function __construct(array $config)
    {
        $this->validate($config);

        $this->connection = Arr::get($config, 'queue.connection');
        $this->options    = Arr::get($config, 'queue.options');
    }

    /**
     * Run setup process required before boot.

     * @return
     */
    public function setup()
    {
        // Do nothing
    }

    /**
     * Run Component Boot process.

     * @return
     */
    public function boot(Application $app, ConfigContract $appConfig)
    {
        // Setup test DB
        $appConfig->set('queue.connections.' . $this->connetion, $this->options);
        $appConfig->set('queue.default', $this->connetion);
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
        if (Arr::has($config, [ 'queue.connection', 'queue.options' ])) {
            return;
        }

        throw new RuntimeException('Queue environment config does not have a valid connection and/or options defined.');
    }

}
