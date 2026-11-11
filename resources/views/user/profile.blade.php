@extends('layouts.mobile', ['title' => 'Profil Pegawai - HadirKU'])

@section('content')
<div class="relative bg-slate-50 min-h-full">
    <!-- Header Section -->
    <div class="bg-app-green text-white px-5 pt-4 pb-16 rounded-b-[36px] shadow-sm relative text-center">
        <div class="flex items-center justify-between mb-4">
            <a href="{{ route('user.dashboard') }}" class="text-white hover:text-emerald-100 p-1.5 -ml-1.5 rounded-lg active:scale-95 transition">
                <i class="fa-solid fa-arrow-left text-lg"></i>
            </a>
            <h1 class="text-base font-bold tracking-tight">Profil Pegawai</h1>
            <div class="w-7"></div>
        </div>

        <!-- Avatar in Header -->
        <div class="inline-block relative">
            <img src="{{ $user->avatar_url }}" alt="Profile" class="w-20 h-20 rounded-full border-4 border-white shadow-lg object-cover mx-auto">
            <span class="absolute bottom-0 right-0 w-5 h-5 bg-emerald-400 border-2 border-white rounded-full"></span>
        </div>
        <h2 class="text-base font-bold text-white mt-2">{{ $user->name }}</h2>
        <p class="text-xs text-emerald-100 font-mono">{{ $user->nik ?? 'HK-PEG' }} &bull; {{ $user->position ?? 'Pegawai' }}</p>
    </div>

    <!-- Main Container -->
    <div class="px-5 -mt-8 relative z-10 space-y-4">
        
        <!-- Info Pegawai Card -->
        <div class="bg-white rounded-3xl p-5 shadow-[0_4px_20px_rgba(0,0,0,0.04)] border border-slate-100 space-y-3">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                <i class="fa-solid fa-id-badge text-emerald-600"></i>
                <span>Informasi Data Diri</span>
            </h3>

            <div class="space-y-2.5 text-xs">
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-400">Email</span>
                    <span class="font-semibold text-slate-700">{{ $user->email }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-400">Nomor HP/WA</span>
                    <span class="font-semibold text-slate-700">{{ $user->phone ?? '-' }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-400">Departemen</span>
                    <span class="font-semibold text-slate-700">{{ $user->department->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50">
                    <span class="text-slate-400">Jam Kerja (Shift)</span>
                    <span class="font-semibold text-slate-700">{{ $user->shift->name ?? 'Shift Regular' }}</span>
                </div>
                <div class="flex justify-between py-1.5">
                    <span class="text-slate-400">Lokasi Kantor Presensi</span>
                    <span class="font-semibold text-emerald-600">{{ $user->office->name ?? 'Kantor Pusat' }}</span>
                </div>
            </div>
        </div>

        <!-- Face Verification Status Card -->
        <div class="bg-white rounded-3xl p-5 border border-slate-100 shadow-sm flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-face-smile"></i>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-800">Verifikasi Wajah Biometrik</h4>
                    <span class="text-[11px] text-slate-400">Kamera Selfie Aktif</span>
                </div>
            </div>
            <span class="text-[11px] font-bold px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-full">
                ✓ Aktif
            </span>
        </div>

        <!-- GPS Geofencing Status Card -->
        <div class="bg-white rounded-3xl p-5 border border-slate-100 shadow-sm flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-location-crosshairs"></i>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-slate-800">Radius Presensi GPS</h4>
                    <span class="text-[11px] text-slate-400">{{ $user->office->radius_meters ?? 150 }} Meter Dari Kantor</span>
                </div>
            </div>
            <span class="text-[11px] font-bold px-2.5 py-1 bg-blue-100 text-blue-800 rounded-full">
                ✓ Geofence On
            </span>
        </div>

        <!-- Switch to Admin button (if admin) -->
        @if($user->role === 'admin')
            <a href="/admin" class="w-full flex items-center justify-center space-x-2 py-3 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-2xl font-bold text-xs transition">
                <i class="fa-solid fa-shield-halved"></i>
                <span>Masuk ke Panel Admin HadirKU</span>
            </a>
        @endif

        <!-- Logout Button -->
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center space-x-2 py-3.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-2xl font-bold text-xs transition">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                <span>Keluar Akun (Logout)</span>
            </button>
        </form>

    </div>
</div>
@endsection
