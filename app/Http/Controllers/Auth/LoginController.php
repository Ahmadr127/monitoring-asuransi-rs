<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SimutuUserSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $throttleKey = Str::lower($request->input('username')).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()
                ->with('error', 'Terlalu banyak percobaan login. Silakan coba lagi dalam '.ceil($seconds / 60).' menit.')
                ->onlyInput('username');
        }

        $remember = $request->filled('remember');

        // Sinkronisasi ringan dari Simutu: segarkan profil + status user yang login.
        try {
            app(SimutuUserSyncService::class)->syncForUsername($credentials['username']);
        } catch (\Throwable $e) {
            Log::warning('Simutu lazy sync gagal saat login: '.$e->getMessage());
        }

        $localUser = User::where('username', $credentials['username'])->first();
        if (
            $localUser
            && $localUser->simutu_status
            && in_array($localUser->simutu_status, config('simutu_sync.block_login_statuses', ['non-aktif']))
        ) {
            return back()->with('error', 'Akun dinonaktifkan di sistem Simutu.')->onlyInput('username');
        }

        if (Auth::attempt($credentials, $remember)) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            $user = Auth::user();
            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }

            if ($user->hasPermission('approval') && ! $user->hasPermission('kerjasama')) {
                return redirect()->route('user.dashboard');
            }

            if ($user->hasPermission('kerjasama')) {
                return redirect()->route('admin.ruang-lingkup.index');
            }

            return redirect()->route('user.kerjasama.index');
        }

        RateLimiter::hit($throttleKey);

        return back()->with('error', 'Username atau password salah.')->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
