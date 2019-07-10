<?php

namespace App\Providers;

use App\Alma\AlmaUsers;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Scriptotek\Alma\Client as AlmaClient;

class AlmaServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(AlmaClient::class, function (Application $app) {
            return new AlmaClient(config('services.alma.key'), 'eu');
        });
        $this->app->singleton(AlmaUsers::class, function (Application $app) {
            return new AlmaUsers($app->make(AlmaClient::class));
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            AlmaClient::class,
            AlmaUsers::class,
        ];
    }
}
