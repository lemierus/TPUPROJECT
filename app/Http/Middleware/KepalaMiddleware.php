<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KepalaMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        if (! auth()->user()->isKepala()) {
            return redirect()->route('login')->with('error', 'Akun Anda tidak memiliki akses ke halaman kepala UPT.');
        }

        return $next($request);
    }
}
