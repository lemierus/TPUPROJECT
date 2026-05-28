<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        if (! auth()->user()->isUser()) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke halaman pengguna.');
        }

        return $next($request);
    }
}
