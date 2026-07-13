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
        // Railway (like most PaaS) terminates TLS at its edge and forwards plain
        // HTTP to the container. Trust the proxy's X-Forwarded-* headers so
        // Laravel knows the original request was HTTPS — otherwise it generates
        // http:// asset URLs that the browser blocks as mixed content.
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\RequireOnboarding::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
