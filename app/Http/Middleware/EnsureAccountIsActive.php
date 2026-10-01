<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('web') ?? $request->user('students');

        if (! $user || ! $user->is_active || $user->locked_at !== null) {
            Auth::guard('web')->logout();
            Auth::guard('students')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['login' => 'This account is no longer active. Please contact an administrator.']);
        }

        return $next($request);
    }
}
