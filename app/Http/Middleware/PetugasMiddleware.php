<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PetugasMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        if (! auth()->user()->isPetugas()) {
            if (auth()->user()->isKepala() && str_starts_with((string) $request->route()?->getName(), 'petugas.master.laporan')) {
                return $next($request);
            }

            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke halaman petugas.');
        }

        return $next($request);
    }
}
