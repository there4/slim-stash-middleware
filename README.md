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
<?php
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
<?php
// Root of the site
// -----------------------------------------------------------------------------
// Simple index page - no data
$app->get('/', function () use ($app) {
    $app->response->allowCache = true;
    $app->response->cacheExpiration = 3600;
    $app->render('index.html');
});

// User Profile Page
// -----------------------------------------------------------------------------
// This would need to be coupled with a cache invalidation on a user change
$app->get('/profile', function () use ($app) {
    $user = $app->currentUser;
    $app->response->allowCache = true;
    $app->response->cacheExpiration = 3600;
    $app->response->signature = 'userProfile' . $user->id;
    $app->render('index.html');
});
```

## Quick API Reference

* __$app->response->allowCache__ `bool` enable caching
* __$app->response->cacheExpiration__ `int` seconds to hold data in cache;
* __$app->response->signature__ `mixed` leave unset for automatic url based. String for simple signature. Callback function will be executed. Array in `call_user_func` format is acceptable as well.


## TODO

* Cache warming script
* Tests
