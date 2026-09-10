<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateAny
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user('web') || $request->user('students')) {
            return $next($request);
        }

        return redirect()->guest(route('login'));
    }
}
