<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KdlhMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        if (! auth()->user()->isKdlh()) {
            return redirect()->route('login')->with('error', 'Akun Anda tidak memiliki akses ke halaman kepala dinas lingkungan hidup.');
        }

        return $next($request);
    }
}
