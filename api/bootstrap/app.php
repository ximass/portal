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
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            'api/register',
            'api/login',
            'api/logout',
        ]);
        
        // Registrar aliases de middleware personalizados
        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'order.permission' => \App\Http\Middleware\CheckOrderPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->report(function (Throwable $e) {
            if (app()->environment('production', 'staging', 'local')) {
                \App\Services\ErrorLogService::log($e, request());
            }
        });
    })->create();
