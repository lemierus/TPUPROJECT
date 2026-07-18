<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
    public function login()
    {
        return view('pages.auth.login');
    }

    public function proses(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        // Checkbox "Ingat Saya" dikirim sebagai string '1' kalau dicentang,
        // dan tidak dikirim sama sekali kalau tidak dicentang.
        // boolean() akan mengonversinya jadi true/false dengan aman.
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            $request->session()->regenerate();

            return redirect()->intended($this->redirectPathFor($user));
        }

        return back()
            ->withInput($request->only('email', 'remember'))
            ->with('error', 'Email atau password salah');
    }

    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function register()
    {
        return view('pages.auth.register');
    }

    public function prosesRegister(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar. Gunakan email lain atau login.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => User::ROLE_USER,
        ]);

        Auth::login($user);

        $this->sendVerificationEmailWithCooldown($user);

        return redirect()->route('verification.notice')
            ->with('success', 'Akun berhasil dibuat. Silakan cek email Anda untuk verifikasi.');
    }

    public function verifyEmailNotice(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect($this->redirectPathFor($user));
        }

        return view('pages.auth.verify-email');
    }

    public function verifyEmail(EmailVerificationRequest $request)
    {
        $user = $request->user();
        $sudahTerverifikasiSebelumnya = $user->hasVerifiedEmail();

        if (! $sudahTerverifikasiSebelumnya) {
            $request->fulfill();
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $pesan = $sudahTerverifikasiSebelumnya
            ? 'Email Anda sudah terverifikasi sebelumnya. Silakan login.'
            : 'Email berhasil diverifikasi. Silakan login untuk melanjutkan.';

        return redirect()->route('login')->with('success', $pesan);
    }

    public function resendVerificationEmail(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect($this->redirectPathFor($user));
        }

        $cooldownUntil = $this->verificationCooldownUntil($user);

        if (now()->lt($cooldownUntil)) {
            $detik = now()->diffInSeconds($cooldownUntil);

            return back()->with('error', "Mohon tunggu {$detik} detik sebelum mengirim ulang email verifikasi.");
        }

        $this->sendVerificationEmailWithCooldown($user);

        return back()->with('success', 'Link verifikasi baru telah dikirim ke email Anda.');
    }

    private function redirectPathFor(User $user): string
    {
        return match ($user->role) {
            User::ROLE_ADMIN => route('admin.dashboard'),
            User::ROLE_KDLH => route('kdlh.dashboard'),
            User::ROLE_PETUGAS => route('petugas.dashboard'),
            User::ROLE_KEPALA => route('kepala.dashboard'),
            default => route('user.dashboard'),
        };
    }

    private function sendVerificationEmailWithCooldown(User $user): CarbonInterface
    {
        event(new Registered($user));

        $until = now()->addMinute();

        Cache::put($this->verificationCooldownKey($user), $until, $until);

        return $until;
    }

    private function verificationCooldownUntil(User $user): CarbonInterface
    {
        return Cache::get($this->verificationCooldownKey($user), now()->subSecond());
    }

    private function verificationCooldownKey(User $user): string
    {
        return 'verification-email-cooldown:' . $user->id;
    }
}