<?php

namespace App\Http\Middleware;

use App\Models\User;
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

        if (! auth()->user()->tpu || ! in_array(auth()->user()->tpu, User::tpuOptions(), true)) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Akun kepala UPT belum memiliki penugasan TPU yang valid.');
        }

        return $next($request);
    }
}
