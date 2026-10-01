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
        $webUser = Auth::guard('web')->user();
        $student = Auth::guard('students')->user();
        $activeAccount = collect([$webUser, $student])->contains(
            fn ($user): bool => $user !== null && $user->is_active && $user->locked_at === null,
        );

        if ($activeAccount) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
