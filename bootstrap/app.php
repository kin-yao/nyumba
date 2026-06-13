<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('web', [
            \App\Http\Middleware\RequireEmailVerification::class,
            \App\Http\Middleware\CheckSubscription::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'mpesa/stk/callback',
            'mpesa/c2b/*/confirmation',
            'mpesa/c2b/*/validation',
        ]);

        $middleware->alias([
            'subscription'   => \App\Http\Middleware\CheckSubscription::class,
            'email.verified' => \App\Http\Middleware\RequireEmailVerification::class,
            'firebase.check' => \App\Http\Middleware\CheckFirebaseAccount::class,
            'admin'          => \App\Http\Middleware\AdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();