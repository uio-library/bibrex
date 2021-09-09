<?php

namespace App\Providers;

use App\Item;
use App\Loan;
use App\Notifications\ExtendedDatabaseNotification;
use App\Thing;
use App\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        Route::pattern('user', '([0-9]+|_new)');
        Route::pattern('item', '([0-9]+|_new)');
        Route::pattern('thing', '([0-9]+|_new)');

        Route::pattern('library', '[0-9]+');
        Route::pattern('user1', '[0-9]+');
        Route::pattern('user2', '[0-9]+');
        Route::pattern('loan', '[0-9]+');
        Route::pattern('reminder', '[0-9]+');
        Route::pattern('ip', '[0-9]+');

        parent::boot();

        Route::bind('thing', function ($value) {
            return $value == '_new'
                ? new Thing(['properties' => []])
                : (auth()->user() ?
                    Thing::withTrashed()
                        ->with('items.loans')
                        ->with('items.allLoans')
                        ->find($value) ?? abort(404)
                    : Thing::withTrashed()
                        ->find($value) ?? abort(404));
        });

        Route::bind('item', function ($value) {
            return $value == '_new'
                ? new Item([ 'library_id' => \Auth::user()->id ])
                : Item::withTrashed()->find($value) ?? abort(404);
        });

        Route::bind('user', function ($value) {
            return $value == '_new'
                ? new User()
                : User::find($value) ?? abort(404);
        });

        Route::bind('loan', function ($value) {
            return Loan::withTrashed()->find($value) ?? abort(404);
        });

        Route::bind('notification', function ($value) {
            return ExtendedDatabaseNotification::with('loan')
                    ->find($value) ?? abort(404);
        });
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        $this->mapWebhooksRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
             ->namespace($this->namespace)
             ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
             ->middleware('api')
             ->namespace($this->namespace)
             ->group(base_path('routes/api.php'));
    }

    /**
     * Define the "webhooks" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapWebhooksRoutes()
    {
        Route::prefix('webhooks')
            ->middleware('webhooks')
            ->namespace($this->namespace)
            ->group(base_path('routes/webhooks.php'));
    }
}
