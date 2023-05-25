<?php

namespace App\Http;

use App\Http\Middleware\Api\CheckComplianceLevelApi;
use App\Http\Middleware\Api\CheckStatus as CheckStatusApi;
use App\Http\Middleware\ApiClient;
use App\Http\Middleware\CheckComplianceLevel;
use App\Http\Middleware\CheckIfProfileIsIndividual;
use App\Http\Middleware\CheckEnableFiatWallets;
use App\Http\Middleware\CheckIfSuperAdmin;
use App\Http\Middleware\CheckMangerPermissions;
use App\Http\Middleware\CheckModuleAvailabilityByProvider;
use App\Http\Middleware\CheckStatus;
use App\Http\Middleware\DefineProject;
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
        \Fruitcake\Cors\HandleCors::class,
        \App\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        DefineProject::class
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
// @see CRATOS-505 Spec -  необходимость перелогинивания пользователя после определённых действий
//            \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            'throttle:60,1',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            ApiClient::class
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
        'check.status' => CheckStatus::class,
        'check.availability' => CheckModuleAvailabilityByProvider::class,
        'super.admin' => CheckIfSuperAdmin::class,
        'check.status.api' => CheckStatusApi::class,
        'restrict.compliance.level.0' => CheckComplianceLevel::class,
        'enable.fiat.wallets' => CheckEnableFiatWallets::class,
        'restrict.compliance.level.0.api' => CheckComplianceLevelApi::class,
        'check.individual' => CheckIfProfileIsIndividual::class,
        'define.project' => DefineProject::class,
        'check.permissions' => CheckMangerPermissions::class,
        'enable.fiat.wallets' => CheckEnableFiatWallets::class,
    ];
}
