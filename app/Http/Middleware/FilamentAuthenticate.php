<?php

namespace App\Http\Middleware;

use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate as BaseAuthenticate;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Support\Facades\Auth;

class FilamentAuthenticate extends BaseAuthenticate
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  array<string>  $guards
     */
    protected function authenticate($request, array $guards): void
    {
        $guard = Filament::auth();

        if (! $guard->check()) {
            $this->unauthenticated($request, $guards);

            return;
        }

        $user = $guard->user();
        $panel = Filament::getCurrentPanel();

        $canAccess = $user instanceof FilamentUser && $user->canAccessPanel($panel);

        if (! $canAccess) {
            // Jika user saat ini bukan admin (misalnya sedang login sebagai pegawai di browser),
            // logout dari sesi web agar tidak terjadi error 403 buntu
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            session()->flash('auth_warning', 'Sesi sebelumnya (' . ($user->name ?? 'User') . ') bukan Administrator. Silakan masuk menggunakan akun Admin.');

            $this->unauthenticated($request, $guards);
            return;
        }

        $this->auth->shouldUse(Filament::getAuthGuard());
    }
}
