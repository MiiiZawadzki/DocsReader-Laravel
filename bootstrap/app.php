<?php

use App\Http\Middleware\JwtCookieToHeader;
use App\Http\Middleware\SetLocale;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        $middleware->prepend(SetLocale::class);
        $middleware->api(prepend: [JwtCookieToHeader::class]);
        $middleware->throttleApi();
        // The login page lives in the frontend app
        $middleware->redirectGuestsTo(fn() => null);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // The API only speaks JSON
        $wantsJson = fn(Request $request) => $request->is('api/*') || $request->expectsJson();

        $exceptions->shouldRenderJsonWhen($wantsJson);

        $exceptions->render(function (Throwable $e, Request $request) use ($wantsJson) {
            if (!$wantsJson($request) || config('app.debug')) {
                return null;
            }

            if ($e instanceof ValidationException
                || $e instanceof AuthenticationException
                || $e instanceof HttpExceptionInterface
            ) {
                return null;
            }

            return response()->json(
                ['message' => __('common::messages.errors.unexpected')],
                500
            );
        });
    })->create();
