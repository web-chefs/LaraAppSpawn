<?php

namespace WebChefs\LaraAppSpawn;

// PHP
use DomainException;
use RuntimeException;

// Framework
use Illuminate\Support\Arr;
use Illuminate\Contracts\Console\Kernel as DefaultKernel;

class ApplicationResolver
{

    /**
     * @var string
     */
    protected $envPath;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $appConfig;

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
    static public function makeApp($envPath = null, array $config = [], $kernel = null)
    {
        $envPath = is_null($envPath) ? static::getRuntimePath() : $envPath;
        $kernel  = is_null($kernel)  ? static::defaultKernel()  : $kernel;
        $config  = empty($config)    ? static::defaultConfig()  : $config;

        return new static($envPath, $kernel, $config);
    }

    /**
     * Create an ApplicationResolver instance.
     *
     * @param string $kernel
     * @param string $envPath
     */
    public function __construct($envPath, $kernel, array $config = [])
    {
        $this->envPath = $envPath;
        $this->config  = $config;

        // Set db path to env path if null
        // Set database path to use fallback
        $dbPath       = Arr::get($this->config, 'database.path');
        $dbPath = $dbPath ?: $this->envPath;
        Arr::set($this->config, 'database.path', $dbPath);

        // Build components and run setup
        $this->setupComponents();

        // Find and make the app
        // If we are in a laravel project try and discover the application
        try {
            $this->app = require $this->discoverApp( $this->envPath );
        }
        // If we are running in a automated build try and include the
        // application from vendor
        catch(DomainException $e) {
            $this->writeBuildConfig(__DIR__);
            $this->app = require $this->getVendorAppPath(__DIR__);
        }

        // Make kernel
        $this->kernel = $this->app->make($kernel);
        $this->kernel->bootstrap();

        // Boot all components
        $this->bootComponents();
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
        if ($this->appConfig) {
            return $this->appConfig;
        }

        return $this->appConfig = $this->app->make('config');
    }

    /**
     * Boot setup components.
     *
     * @return void
     */
    protected function bootComponents()
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
    protected function setupComponents()
    {
        $components = Arr::get($this->config, 'components');
        foreach ($components as $component) {
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
        $path = dirname($path);

        // Check if we have reached the end
        if ($path == '.' || $path == DIRECTORY_SEPARATOR) {
            throw new DomainException('Laravel "bootstramp/app.php" could not be discovered.');
        }

        // Try again (recursive)
        return $this->discoverApp($path);
    }

    /**
     * Update the vendor location of config/app.php include our service provider
     *
     * @param  string $basePath
     *
     * @return void
     */
    protected function writeBuildConfig($basePath)
    {
        $configPath = $this->getVendorAppConfig($basePath);

        if (! is_writable($configPath)) {
            throw new RuntimeException('The config/app.php file must be present and writable.');
        }

        $config = $this->buildAppConfig($configPath);

        file_put_contents($configPath, '<?php return '.var_export($config, true).';');
    }

    /**
     * Join an array and base bath correctly as a file system path.
     *
     * @param  string $basePath
     * @param  array  $pathParts
     *
     * @return string
     */
    protected function makePath($basePath, $pathParts)
    {
        return $basePath . DIRECTORY_SEPARATOR . join(DIRECTORY_SEPARATOR, $pathParts);
    }

    /**
     * Return the parts that should lead to the laravel route found in vendor.
     *
     * @return array
     */
    protected function getVendorAppRoot()
    {
        return [
            '..',
            'vendor',
            'laravel',
            'laravel',
        ];
    }

    /**
     * Build a path to boostrap/app.php assuming laravel/laravel is a package
     * under vendor. This will only be the case in automated builds for testing
     * purposes.
     *
     * @param  string $path "__DIR__ . '/vendor/laravel/laravel/bootstrap/app.php'"
     *
     * @return string
     */
    protected function getVendorAppPath($path)
    {
        return $this->makePath($path, array_merge($this->getVendorAppRoot(), ['bootstrap', 'app.php']));
    }

    /**
     * Build the path to config/app.php when laravel is in vendor and we are
     * running in an automated travis build.
     *
     * @return string
     */
    protected function getVendorAppConfig($basePath)
    {
        return $this->makePath($basePath, array_merge($this->getVendorAppRoot(), ['config', 'app.php']));
    }

    /**
     * default fallback application kernel.
     *
     * @return DefaultKernel
     */
    static public function defaultKernel()
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
    static public function getRuntimePath($depth = 2)
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
    static public function defaultConfig()
    {
        return require __DIR__ . '/ConfigDefaults.php';
    }

}