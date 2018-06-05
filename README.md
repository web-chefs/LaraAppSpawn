# LaraAppSpawn

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]
[![Build Status][ico-travis]][link-travis]

Laravel Custom Application Spawner, primarily used creating a Application instance in a unit testing environment allowing you to interact with Laravel in our tests.

By default the it will use a SQLite in memory database allowing you run migrations and use a fully functional database during your tests.

## Install

__Via Composer__

``` bash
$ composer require web-chefs/laravel-app-spawn
```

## Basic usage example

```php

use Illuminate\Foundation\Testing\TestCase;
use WebChefs\LaraAppSpawn\ApplicationResolver;

class MyTest extends TestCase
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        // Root of my app, used as a fallback for location of /database when
        // database.path in config is null.
        $appRoutePath = __DIR__;

        // Resolve Application
        $resolver  = ApplicationResolver::makeApp($appRoutePath);
        $this->app = $resolver->app();

        // Run our database migrations if required
        $this->artisan('migrate:refresh', [ '--force' => 1 ]);

        return $this->app;
    }

}
```

## Example with custom configs

```php
use Illuminate\Support\Arr;
use Illuminate\Foundation\Testing\TestCase;
use WebChefs\LaraAppSpawn\ApplicationResolver;

class MyTest extends TestCase
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        // Root of my app, used as a fallback for location of /database when
        // database.path in config is null.
        $appRoutePath = __DIR__;

        // Build Resolver config
        $config = ApplicationResolver::defaultConfig();
        Arr::set($config, 'database.connection', $this->connectionName);
        Arr::set($config, 'queue.connection', $this->connectionName);

        // Resolve Application
        $resolver  = ApplicationResolver::makeApp($appRoutePath, $config);
        $this->app = $resolver->app();

        // Run our database migrations if required
        $this->artisan('migrate:refresh', [ '--force' => 1 ]);

        return $this->app;
    }

}
```

## TravisCI

This was originally developed for `WebChefs\LaraAppSpawn` and for testing multiple version of Laravel using the same tests.

To see how that is possible see WebChefs\LaraAppSpawn [.travis.yml](https://github.com/web-chefs/LaraAppSpawn/blob/master/.travis.yml).

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

All code submissions will only be evaluated and accepted as pull-requests. If you have any questions or find any bugs please feel free to open an issue.

## Credits

- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/web-chefs/laravel-app-spawn.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/web-chefs/laravel-app-spawn.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/web-chefs/LaraAppSpawn/master.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/web-chefs/laravel-app-spawn
[link-travis]: https://travis-ci.org/web-chefs/LaraAppSpawn
[link-downloads]: https://packagist.org/packages/web-chefs/laravel-app-spawn
[link-author]: https://github.com/JFossey
[link-contributors]: ../../contributors