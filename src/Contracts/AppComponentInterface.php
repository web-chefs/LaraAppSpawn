<?php

namespace WebChefs\LaraAppSpawn\Contracts;

// Framework
use Illuminate\Contracts\Config\Repository as ConfigContract;
use Illuminate\Contracts\Foundation\Application;

interface AppComponentInterface
{

    /**
     * Run setup process required before boot.
     *
     * @return
     */
    public function setup();

    /**
     * Run Component Boot process.
     *
     * @return
     */
    public function boot(Application $app, ConfigContract $appConfig);

}