<?php

namespace App\Http;

use App\Configuration;
use App\Http\Middleware\ApiMiddleware;
use App\Http\Middleware\AuthenticatedByPassportViaCookie;
use App\Http\Middleware\AuthenticatedByPassportViaRequest;
use App\Http\Middleware\AuthorizedWithAdmin;
use App\Http\Middleware\AuthorizedWithPermissions;
use App\Http\Middleware\Device;
use App\Http\Middleware\OverrideAuthorizationHeader;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        // \Fruitcake\Cors\HandleCors::class,
        \App\Http\Middleware\CheckForMaintenanceMode::class,
        \App\Http\Middleware\CheckForClientLimitation::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            // \App\Http\Middleware\EncryptCookies::class,
            // \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            // \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            // \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            // \App\Http\Middleware\VerifyCsrfToken::class,
            // \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            'throttle:' . Configuration::THROTTLE_REQUEST_MAX_ATTEMPTS . ',' . Configuration::THROTTLE_REQUEST_DECAY_MINUTES,
            OverrideAuthorizationHeader::class,
            Device::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

        'device' => Device::class,
        'authenticated.passport.cookie' => AuthenticatedByPassportViaCookie::class,
        'authenticated.passport.request' => AuthenticatedByPassportViaRequest::class,
        'authorized.permissions' => AuthorizedWithPermissions::class,
        'authorized.admin' => AuthorizedWithAdmin::class,
    ];

    protected $middlewarePriority = [
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
        \Illuminate\Routing\Middleware\ThrottleRequests::class,

        OverrideAuthorizationHeader::class,

        \Illuminate\Session\Middleware\AuthenticateSession::class,

        AuthenticatedByPassportViaCookie::class,
        AuthenticatedByPassportViaRequest::class,
        \App\Http\Middleware\Authenticate::class,
        Device::class,

        \Illuminate\Routing\Middleware\SubstituteBindings::class,

        AuthorizedWithAdmin::class,
        AuthorizedWithPermissions::class,

        \Illuminate\Auth\Middleware\Authorize::class,
    ];
}