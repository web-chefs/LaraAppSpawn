<?php

namespace WebChefs\LaraAppSpawn\Tests;

// Package
use WebChefs\LaraAppSpawn\ApplicationResolver;

// Framework
use Illuminate\Support\Arr;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase;

// Aliases
use DB;

class SpawnTest extends TestCase
{

    /**
     * @var string
     */
    protected $connectionName = 'spawn_test';

    /**
     * @var ApplicationResolver
     */
    protected $spawn;

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        // Build Resolver config
        $config = ApplicationResolver::defaultConfig();
        Arr::set($config, 'database.connection', $this->connectionName);

        // Add custom element to config
        $callback = function(array $config) {
            $config['spawn_test'] = TRUE;
            return $config;
        };
        Arr::set($config, 'callback.vendor_config', $callback);

        // Resolve Application
        $this->spawn = ApplicationResolver::makeApp(__DIR__, $config);
        $this->app   = $this->spawn->app();

        return $this->app;
    }

    /**
     * Test our setup correctly created our application object
     *
     * @return void
     */
    public function testSpawnApp()
    {
        // Confirm our application was created and is the correct type.
        $this->assertInstanceOf(Application::class, $this->app);
    }

    /**
     * Test our setup correctly created our application object
     *
     * @return void
     */
    // public function testSpawnConfig()
    // {
    //     $this->arrayHasKey('spawn_test', $this->spawn->config()->all());

    //     $this->assertTrue($this->spawn->config()->get('spawn_test'));
    // }

    /**
     * Test our test queue was setup correctly and and is empty.
     *
     * @return void
     */
    public function testDefaultDB()
    {
        // Check our setup is using our in memory database connection
        $this->assertEquals(DB::getDefaultConnection(), $this->connectionName);
    }

}