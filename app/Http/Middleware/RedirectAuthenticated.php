<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('web')->check() || Auth::guard('students')->check()) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
