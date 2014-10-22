Slim Stash Cache Middleware
================================================================================
> Caching middleware layer for Slim using Stash

## About

A simple middleware layer for Slim that provides a caching mechanism for
endpoints.

## Example Setup

Here is an example of the middleware setup. It's using a config setting to
enable the cache.

```php
// Stash Page Cache Middleware
// -----------------------------------------------------------------------------
// A generalized way of caching the output of an endpoint
$app->container->singleton('stash', function () use ($app) {
    if (!is_dir($app->config('caches.path'))) {
        mkdir($app->config('caches.path'), 0777, true);
    }
    $stashFileSystem = new \Stash\Driver\FileSystem(array(
        'path' => $app->config('caches.path')
    ));
    return new \Stash\Pool($stashFileSystem);
});

if ($app->config('enable_cache')) {
    $app->add(new \There4\Slim\Middleware\StashCache($app->stash));
}
```

And an endpoint that uses the cache:

```php

// Root of the site
// -----------------------------------------------------------------------------
// Simple index page - no data
$app->get('/', function () use ($app) {
    $app->response->allowCache = true;
    $app->response->cacheExpiration = 3600;
    $app->render('index.html');
});
```

## TODO

* Add cache signature key generator callback option
* Cache warming script
* Tests
* Improved documentation
* Packagist publication
