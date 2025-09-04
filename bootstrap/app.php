<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Webfox\Xero\Exceptions\OAuthException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auto.refresh.xero' => \App\Http\Middleware\AutoRefreshXeroToken::class,
            'subscription.access' => \App\Http\Middleware\CheckSubscriptionAccess::class,
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Gracefully handle user cancelling Xero consent and any OAuthException from the package
        $exceptions->renderable(function (OAuthException $e, Request $request) {
            return redirect()->route('dashboard')
                ->withErrors('Xero connection was cancelled. You can connect your Xero account from the dashboard.');
        });

        // Defensive: if callback includes access_denied without throwing first
        $exceptions->renderable(function (\Throwable $e, Request $request) {
            if ($request->routeIs('xero.auth.callback') && $request->get('error') === 'access_denied') {
                return redirect()->route('dashboard')
                    ->withErrors('Xero connection was cancelled. You can connect your Xero account from the dashboard.');
            }
        });
    })->create();
