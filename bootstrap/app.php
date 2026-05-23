<?php

use App\Http\Middleware\JwtCookieToHeader;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        $middleware->prepend(SetLocale::class);
        $middleware->api(prepend: [JwtCookieToHeader::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
