<?php

namespace App\Providers;

use App\View\Composers\AppLayoutComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFour();
        View::composer('layouts.admin', AppLayoutComposer::class);

        RateLimiter::for('login', function (Request $request): Limit {
            $identifier = Str::transliterate(Str::lower((string) $request->string('login')));

            return Limit::perMinute(5)->by($identifier.'|'.$request->ip());
        });
        RateLimiter::for('registration', fn (Request $request): Limit => Limit::perMinute(5)->by($request->ip()));
        RateLimiter::for('password-email', function (Request $request): Limit {
            $email = Str::transliterate(Str::lower((string) $request->string('email')));

            return Limit::perMinute(3)->by($email.'|'.$request->ip());
        });
        RateLimiter::for('document-upload', fn (Request $request): Limit => Limit::perMinute(20)->by(
            (string) ($request->user()?->getAuthIdentifier() ?? $request->ip()),
        ));
        RateLimiter::for('workflow-actions', fn (Request $request): Limit => Limit::perMinute(30)->by(
            (string) ($request->user()?->getAuthIdentifier() ?? $request->ip()),
        ));

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
