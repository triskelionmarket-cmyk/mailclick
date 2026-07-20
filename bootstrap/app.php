<?php

// Suppress PHP 8.5 deprecation notices (vendor packages)
error_reporting(E_ALL & ~E_DEPRECATED);
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->use([
            \Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks::class,
            // \Illuminate\Http\Middleware\TrustHosts::class,
            // \Illuminate\Http\Middleware\TrustProxies::class,
            \Illuminate\Http\Middleware\HandleCors::class,
            \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \Illuminate\Http\Middleware\ValidatePostSize::class,
            \Illuminate\Foundation\Http\Middleware\TrimStrings::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        ]);

        $middleware->appendToGroup('web', [
            \Acelle\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            // \Acelle\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->prependToGroup('web_nocsrf', [
            \Acelle\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            #\Acelle\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->prependToGroup('api', [
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->alias([
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
            'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
            'can' => \Illuminate\Auth\Middleware\Authorize::class,
            'guest' => \Acelle\Http\Middleware\RedirectIfAuthenticated::class,
            'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
            'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'frontend' => \Acelle\Http\Middleware\Frontend::class,
            'backend' => \Acelle\Http\Middleware\Backend::class,
            'installed' => \Acelle\Http\Middleware\Installed::class,
            'not_installed' => \Acelle\Http\Middleware\NotInstalled::class,
            'not_logged_in' => \Acelle\Http\Middleware\NotLoggedIn::class,
            'subscription' => \Acelle\Http\Middleware\Subscription::class,
            'email_verify' => \Acelle\Http\Middleware\EmailVerify::class,
            '2fa' => \Acelle\Http\Middleware\TwoFA::class,
            'setdb' => \Acelle\Http\Middleware\SetUserDbConnection::class,
            'api_key_auth' => \Acelle\Http\Middleware\ApiKeyAuth::class,
        ]);

        $middleware->priority([
            \Acelle\Http\Middleware\NotInstalled::class,
            \Illuminate\Auth\Middleware\Authenticate::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
            'plugins/webhooks/*',
            'delivery/*',
            'api/*',
            '*/embedded-form-*',
            'payments/stripe/credit-card*',
            'frontend/*',
            'paytr/*'
        ]);
    })

    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();