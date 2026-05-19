<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
        );

        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->report(function (TokenMismatchException $e) {
            $request = request();
            $session = $request->hasSession() ? $request->session() : null;
            $sessionToken = $session?->token();
            $requestToken = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');
            $xsrfHeader = $request->header('X-XSRF-TOKEN');
            $sessionCookieName = config('session.cookie');

            Log::error('CSRF token mismatch detected', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'host' => $request->getHost(),
                'scheme' => $request->getScheme(),
                'is_secure' => $request->isSecure(),
                'session_driver' => config('session.driver'),
                'session_connection' => config('session.connection'),
                'session_domain' => config('session.domain'),
                'session_secure_cookie' => config('session.secure'),
                'session_same_site' => config('session.same_site'),
                'session_cookie_name' => $sessionCookieName,
                'has_session_cookie' => $request->cookies->has($sessionCookieName),
                'session_id' => $session?->getId(),
                'has_session_token' => filled($sessionToken),
                'has_request_token' => filled($requestToken),
                'has_xsrf_header' => filled($xsrfHeader),
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });
    })->create();
