<?php

namespace App\Http\Controllers;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            if (Auth::user()->role === 'admin') {
                return redirect('/admin');
            }
            return redirect('/dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'Email atau NIK wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        // Find user by email or NIK
        $user = User::where('email', $credentials['login'])
            ->orWhere('nik', $credentials['login'])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'login' => 'Email / NIK atau kata sandi yang Anda masukkan salah.',
            ])->onlyInput('login');
        }

        if (! $user->is_active) {
            return back()->withErrors([
                'login' => 'Akun Anda dinonaktifkan. Silakan hubungi admin.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if ($user->role === 'admin') {
            return redirect()->intended('/admin');
        }

        return redirect()->intended('/dashboard');
    }

    public function loginAdmin(Request $request)
    {
        $data = $request->input('data', []);

        $login = $request->input('login')
            ?? (is_array($data) ? ($data['email'] ?? $data['login'] ?? null) : null)
            ?? $request->input('email');

        $password = $request->input('password')
            ?? (is_array($data) ? ($data['password'] ?? null) : null);

        $remember = (bool) (
            $request->input('remember')
            ?? (is_array($data) ? ($data['remember'] ?? false) : false)
        );

        if (! $login || ! $password) {
            return back()->withErrors([
                'email' => 'Email / NIK dan kata sandi wajib diisi.',
            ])->onlyInput('email', 'login');
        }

        $user = User::where('email', $login)
            ->orWhere('nik', $login)
            ->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return back()->withErrors([
                'email' => 'Email / NIK atau kata sandi salah.',
            ])->onlyInput('email', 'login');
        }

        if (! $user->is_active) {
            return back()->withErrors([
                'email' => 'Akun Anda dinonaktifkan. Silakan hubungi admin.',
            ]);
        }

        $panel = Filament::getCurrentPanel() ?? Filament::getPanel('admin');
        if (! $user->canAccessPanel($panel)) {
            return back()->withErrors([
                'email' => 'Akun Anda tidak memiliki izin akses ke panel admin.',
            ]);
        }

        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->intended('/admin');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Anda telah berhasil keluar.');
    }
}
