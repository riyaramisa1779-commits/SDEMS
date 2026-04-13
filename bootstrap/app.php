<?php

use App\Http\Middleware\CheckAccountLocked;
use App\Http\Middleware\EnsureUserRank;
use App\Http\Middleware\TrackLoginDevice;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Named (alias) middleware
        $middleware->alias([
            'rank'           => EnsureUserRank::class,
            'account.locked' => CheckAccountLocked::class,
            'track.device'   => TrackLoginDevice::class,
            // Spatie role/permission middleware
            'role'           => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'     => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        // Apply device tracking globally to web routes
        $middleware->web(append: [
            TrackLoginDevice::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
