<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'approved' => \App\Http\Middleware\EnsureServicemanApproved::class,
        ]);
        
        // Apply approval check to web routes
        $middleware->web(append: [
            \App\Http\Middleware\EnsureServicemanApproved::class,
        ]);
        
        // Apply API error handling middleware to all API routes
        $middleware->api(prepend: [
            \App\Http\Middleware\HandleApiErrors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
