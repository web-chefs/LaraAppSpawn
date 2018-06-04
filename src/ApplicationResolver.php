<?php

namespace WebChefs\LaraAppSpawn;

// PHP
use DomainException;

// Framework
use Illuminate\Support\Arr;
use Illuminate\Contracts\Console\Kernel as DefaultKernel;

class ApplicationResolver
{

    /**
     * Array of components to load in the order they should be setup.
     *
     * @var array
     */
    public static $components = [
        \WebChefs\LaraAppSpawn\Components\Database::class,
        \WebChefs\LaraAppSpawn\Components\Queue::class,
    ];

    /**
     * @var string
     */
    protected $envPath;

    /**
     * @var array
     */
    protected $config;

    /**
     * Component instances
     *
     * @var array
     */
    protected $componentInstances = [];

    /**
     * @var Kernel
     */
    protected $kernel;

    /**
     * @var Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * Static factor constructor.
     *
     * @param  string $kernel
     * @param  string $envPath
     * @param  array  $segments
     *
     * @return Kernel instance
     */
    static public function makeApp($kernel = null, $envPath = null, array $config = [])
    {
        $envPath = is_null($envPath) ? static::getRuntimePath() : $envPath;
        $kernel  = is_null($kernel)  ? static::defaultKernel()  : $kernel;
        $config  = empty($config)    ? static::defaultConfig()  : $config;

        return new static($kernel, $envPath, $config);
    }

    /**
     * Create an ApplicationResolver instance.
     *
     * @param string $kernel
     * @param string $envPath
     */
    public function __construct($kernel, $envPath, array $config = [])
    {
        $this->envPath = $envPath;
        $this->config  = $config;

        // Set db path to env path if null
        $dbPath = Arr::get($this->config, 'database.path', $this->envPath);
        Arr::set($this->config, 'database.path', $dbPath);

        $this->BuilSetupComponents();
        $this->app = require $this->discoverApp( $this->envPath );

        $this->kernel = $this->app->make($kernel);
        $this->kernel->bootstrap();
    }

    /**
     * Get App Kernel.
     *
     * @return Kernel
     */
    public function app()
    {
        return $this->app;
    }

    /**
     * Get Bootstrap Application.
     *
     * @return Application
     */
    public function kernel()
    {
        return $this->kernel;
    }

    /**
     * Environmental path.
     *
     * @return string
     */
    public function envPath()
    {
        return $this->envPath;
    }

    /**
     * Get application config repository;
     *
     * @return Repository
     */
    public function config()
    {
        return $this->app->make('config');
    }

    /**
     * Boot setup components.
     *
     * @return void
     */
    public function bootComponents()
    {
        $config = $this->config();

        foreach ($this->componentInstances as $instance) {
            $instance->boot($this->app, $config);
        }
    }

    /**
     * Build component instances and run setup method.
     *
     * @return void
     */
    protected function BuilSetupComponents()
    {
        foreach (static::$components as $component) {
            $instance = new $component($this->config);
            $instance->setup();

            $this->componentInstances[ $component ] = $instance;
        }
    }

    /**
     * A recursive method that works backwards through the directory structure
     * until it finds "bootstrap/app.php".
     *
     * This should normally resolve to __DIR__ . '../../boostrap/app.php'
     *
     * @param  string $path
     *
     * @return string
     */
    protected function discoverApp($path)
    {
        $file = $path . DIRECTORY_SEPARATOR . join(DIRECTORY_SEPARATOR, ['bootstrap', 'app.php']);

        if (file_exists($file)) {
            return $file;
        }

        // Go up a level
        $path = dirname($path, 1);

        // Check if we have reached the end
        if ($path == '.' || $path == DIRECTORY_SEPARATOR) {
            throw new DomainException('Lravel "bootstramp/app.php" could not be discovered.');
        }

        // Try again (recursive)
        return $this->discoverApp($path);
    }

    /**
     * default fallback application kernel.
     *
     * @return DefaultKernel
     */
    static protected function defaultKernel()
    {
        return DefaultKernel::class;
    }

    /**
     * Get the file location of the calling file.
     *
     * @param  integer $depth
     *
     * @return string
     */
    static protected function getRuntimePath($depth = 2)
    {
        $stack = debug_backtrace();
        $frame = $stack[count($stack) - $depth];
        return dirname($frame['file']);
    }

    /**
     * Default fallback config.
     *
     * @return array
     */
    static protected function defaultConfig()
    {
        return require __DIR__ . '/ConfigDefaults.php';
    }

}