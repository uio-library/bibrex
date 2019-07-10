<?php

namespace App\Http\Middleware;

use Closure;
use Sentry\State\HubInterface;
use Sentry\State\Scope;

class SentryContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (app()->bound('sentry')) {
            /** @var HubInterface $sentry */
            $sentry = app('sentry');

            // Add user context
            $sentry->configureScope(function (Scope $scope): void {
                if (auth()->check()) {
                    $user = auth()->user();
                    $scope->setUser(['id' => $user->id, 'username' => $user->name]);
                } else {
                    $scope->setUser(['id' => null]);
                }
            });
        }

        return $next($request);
    }
}
