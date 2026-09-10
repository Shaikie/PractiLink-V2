<?php

use App\Http\Middleware\AuthenticateAny;
use App\Http\Middleware\EnsurePermission;
use App\Http\Middleware\RedirectAuthenticated;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')->group(base_path('routes/auth.php'));
            Route::middleware(['web', 'auth.any'])->group(base_path('routes/dashboard.php'));
            Route::middleware(['web', 'auth:web'])->group(base_path('routes/profile.php'));
            Route::middleware(['web', 'auth:students'])->group(base_path('routes/student.php'));
            Route::middleware(['web', 'auth.any'])->group(base_path('routes/notifications.php'));
            Route::middleware(['web', 'auth:web'])->prefix('admin')->name('admin.')->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth.any' => AuthenticateAny::class,
            'guest.any' => RedirectAuthenticated::class,
            'permission' => EnsurePermission::class,
        ]);
        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo('/dashboard');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
