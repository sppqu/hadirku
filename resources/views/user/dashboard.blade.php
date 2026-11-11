@extends('layouts.mobile', ['title' => 'Dashboard Kehadiran - HadirKU'])

@section('content')
<div class="relative bg-slate-50 min-h-full">
    <!-- Header Section (Green background with Greeting & Notification) -->
    <div class="bg-app-green text-white px-5 pt-4 pb-28 relative">
        <div class="flex items-center justify-between mb-4">
            <!-- Hamburger menu button -->
            <button type="button" onclick="toggleDrawer()" class="text-white hover:text-emerald-100 p-1.5 -ml-1.5 rounded-lg active:scale-95 transition">
                <i class="fa-solid fa-bars text-xl"></i>
            </button>

            <!-- Notification Bell with Badge -->
            <div class="relative">
                <button type="button" onclick="showNotifications()" class="text-white hover:text-emerald-100 p-1.5 rounded-lg active:scale-95 transition">
                    <i class="fa-regular fa-bell text-xl"></i>
                </button>
                <span class="absolute top-1 right-1 w-4 h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center border-2 border-app-green">3</span>
            </div>
        </div>

        <div class="space-y-0.5">
            <h1 class="text-xl font-bold tracking-tight flex items-center gap-1.5">
                {{ $greeting }}, {{ explode(' ', $user->name)[0] }}! 👋
            </h1>
            <p class="text-emerald-100 text-xs font-normal">Have a productive day ahead.</p>
        </div>
    </div>

    <!-- Main Container (Elevated with relative z-10 so it sits ON TOP of the green background) -->
    <div class="px-5 -mt-20 relative z-10 space-y-4">

        <!-- Card: Today's Status (Matches Screenshot 1) -->
        <div class="bg-white rounded-3xl p-5 shadow-[0_10px_25px_-5px_rgba(0,0,0,0.06),0_8px_10px_-6px_rgba(0,0,0,0.04)] border border-slate-100">
            <div class="mb-3">
                <h3 class="text-xs font-bold text-slate-800">Today's Status</h3>
                <p class="text-[11px] text-slate-400 font-medium">{{ $today->translatedFormat('d F Y, l') }}</p>
            </div>

            <div class="flex items-center justify-between pt-1">
                <!-- Check In Column -->
                <div class="flex-1 text-center">
                    <span class="text-[11px] font-medium text-slate-400 block mb-1">Check In</span>
                    <span class="text-sm font-extrabold text-app-green block tracking-tight">
                        {{ $punchInTime }}
                    </span>
                </div>

                <!-- Center Working Hours Circular Progress Ring -->
                <div class="relative w-28 h-28 flex items-center justify-center flex-shrink-0">
                    <svg class="w-full h-full -rotate-90 transform" viewBox="0 0 100 100">
                        <!-- Background Circle -->
                        <circle cx="50" cy="50" r="40" stroke="#F1F5F9" stroke-width="8" fill="transparent"></circle>
                        <!-- Dynamic Progress Circle -->
                        @php
                            $circumference = 2 * 3.14159 * 40;
                            $strokeDashoffset = $circumference - ($workingProgressPercent / 100) * $circumference;
                        @endphp
                        <circle 
                            cx="50" 
                            cy="50" 
                            r="40" 
                            stroke="#059669" 
                            stroke-width="8" 
                            stroke-linecap="round" 
                            fill="transparent"
                            stroke-dasharray="{{ $circumference }}"
                            stroke-dashoffset="{{ $strokeDashoffset }}"
                            class="transition-all duration-1000 ease-out"
                        ></circle>
                    </svg>

                    <!-- Text Inside Circle -->
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                        <span class="text-[9px] text-slate-400 font-medium leading-none">Working Hours</span>
                        <span class="text-lg font-black text-slate-800 leading-tight mt-0.5">{{ $workingHoursFormatted }}</span>
                        <span class="text-[9px] text-slate-400 font-semibold leading-none">Hrs</span>
                    </div>
                </div>

                <!-- Check Out Column -->
                <div class="flex-1 text-center">
                    <span class="text-[11px] font-medium text-slate-400 block mb-1">Check Out</span>
                    <span class="text-sm font-extrabold text-slate-400 block tracking-tight">
                        {{ $punchOutTime }}
                    </span>
                </div>
            </div>
        </div>

        <!-- 4 Quick Stat Pill Cards (Grid 4 columns) -->
        <div class="grid grid-cols-4 gap-2">
            <!-- Punch In -->
            <div class="bg-white p-2.5 rounded-2xl border border-slate-100 shadow-sm text-center flex flex-col items-center justify-center">
                <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center mb-1.5 text-xs">
                    <i class="fa-regular fa-calendar-check"></i>
                </div>
                <span class="text-[10px] font-medium text-slate-400 block">Punch In</span>
                <span class="text-[11px] font-bold text-slate-800 block mt-0.5">{{ $punchInTime }}</span>
            </div>

            <!-- Punch Out -->
            <div class="bg-white p-2.5 rounded-2xl border border-slate-100 shadow-sm text-center flex flex-col items-center justify-center">
                <div class="w-8 h-8 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center mb-1.5 text-xs">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </div>
                <span class="text-[10px] font-medium text-slate-400 block">Punch Out</span>
                <span class="text-[11px] font-bold text-slate-800 block mt-0.5">{{ $punchOutTime }}</span>
            </div>

            <!-- Break -->
            <div class="bg-white p-2.5 rounded-2xl border border-slate-100 shadow-sm text-center flex flex-col items-center justify-center">
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center mb-1.5 text-xs">
                    <i class="fa-solid fa-mug-saucer"></i>
                </div>
                <span class="text-[10px] font-medium text-slate-400 block">Break</span>
                <span class="text-[11px] font-bold text-slate-800 block mt-0.5">{{ $breakDurationFormatted }}</span>
            </div>

            <!-- Total Hours -->
            <div class="bg-white p-2.5 rounded-2xl border border-slate-100 shadow-sm text-center flex flex-col items-center justify-center">
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-1.5 text-xs">
                    <i class="fa-regular fa-clock"></i>
                </div>
                <span class="text-[10px] font-medium text-slate-400 block">Total Hours</span>
                <span class="text-[11px] font-bold text-slate-800 block mt-0.5">{{ $workingHoursFormatted }}</span>
            </div>
        </div>

        <!-- Attendance Overview (Weekly Bar) -->
        <div class="bg-white rounded-3xl p-4 border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-bold text-slate-800">Attendance Overview</h3>
                <a href="{{ route('user.history') }}" class="text-[11px] font-bold text-emerald-600 hover:text-emerald-700">View Calendar</a>
            </div>

            <div class="grid grid-cols-6 gap-1 text-center">
                @foreach($weekDays as $day)
                    <div class="flex flex-col items-center py-2 px-1 rounded-2xl transition {{ $day['is_today'] ? 'bg-emerald-50 border border-emerald-200' : 'bg-slate-50/50' }}">
                        <span class="text-[10px] font-medium {{ $day['is_today'] ? 'text-emerald-700 font-bold' : 'text-slate-400' }}">{{ $day['day_name'] }}</span>
                        <span class="text-xs font-bold {{ $day['is_today'] ? 'text-emerald-800' : 'text-slate-700' }} mt-0.5">{{ $day['day_number'] }}</span>
                        
                        <div class="my-1.5">
                            @if($day['has_checked_in'])
                                <span class="w-4 h-4 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[8px] mx-auto">
                                    <i class="fa-solid fa-check"></i>
                                </span>
                            @elseif($day['is_today'])
                                <span class="w-4 h-4 rounded-full border-2 border-emerald-500 border-dashed flex items-center justify-center mx-auto"></span>
                            @else
                                <span class="w-4 h-4 rounded-full bg-slate-200 text-slate-400 flex items-center justify-center text-[8px] mx-auto">
                                    <i class="fa-solid fa-minus"></i>
                                </span>
                            @endif
                        </div>

                        <span class="text-[9px] font-semibold {{ $day['is_today'] ? 'text-emerald-700' : 'text-slate-500' }}">
                            {{ $day['is_today'] ? 'Today' : $day['check_in_time'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- This Month Summary -->
        <div class="bg-white rounded-3xl p-4 border border-slate-100 shadow-sm">
            <h3 class="text-xs font-bold text-slate-800 mb-3">This Month Summary</h3>
            <div class="grid grid-cols-4 gap-2 text-center">
                <!-- Present -->
                <div class="p-2">
                    <span class="text-lg font-black text-emerald-600 block leading-tight">{{ $presentCount }}</span>
                    <span class="text-[10px] font-semibold text-slate-400 block mt-0.5">Present</span>
                </div>
                <!-- Absent -->
                <div class="p-2">
                    <span class="text-lg font-black text-red-500 block leading-tight">{{ $absentCount }}</span>
                    <span class="text-[10px] font-semibold text-slate-400 block mt-0.5">Absent</span>
                </div>
                <!-- Half Day / Late -->
                <div class="p-2">
                    <span class="text-lg font-black text-amber-500 block leading-tight">{{ $lateCount }}</span>
                    <span class="text-[10px] font-semibold text-slate-400 block mt-0.5">Half Day</span>
                </div>
                <!-- Total Hours -->
                <div class="p-2">
                    <span class="text-lg font-black text-blue-600 block leading-tight">{{ $totalMonthHoursFormatted }}</span>
                    <span class="text-[10px] font-semibold text-slate-400 block mt-0.5">Total Hours</span>
                </div>
            </div>
        </div>

        <!-- Recent Activities (Timeline List) -->
        <div class="bg-white rounded-3xl p-4 border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-bold text-slate-800">Recent Activities</h3>
                <a href="{{ route('user.history') }}" class="text-[11px] font-bold text-emerald-600 hover:text-emerald-700">View All</a>
            </div>

            <div class="space-y-3">
                @forelse($recentActivities as $act)
                    <div class="flex items-center justify-between py-1 border-b border-slate-50 last:border-0">
                        <div class="flex items-center space-x-3">
                            <!-- Icon Type -->
                            @if($act->type === 'check_in')
                                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm flex-shrink-0">
                                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                                </div>
                            @elseif($act->type === 'check_out')
                                <div class="w-9 h-9 rounded-xl bg-red-50 text-red-500 flex items-center justify-center text-sm flex-shrink-0">
                                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                </div>
                            @else
                                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center text-sm flex-shrink-0">
                                    <i class="fa-solid fa-mug-saucer"></i>
                                </div>
                            @endif

                            <div>
                                <h4 class="text-xs font-bold text-slate-800">{{ $act->type_label }}</h4>
                                <span class="text-[10px] text-slate-400 font-medium">
                                    {{ $act->formatted_time }} &bull; {{ \Carbon\Carbon::parse($act->date)->translatedFormat('d M Y') }}
                                </span>
                            </div>
                        </div>

                        <!-- Location Pin Badge -->
                        <div class="flex items-center text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">
                            <i class="fa-solid fa-location-dot text-[9px] mr-1"></i>
                            <span>{{ $act->location_name ?? 'Office' }}</span>
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-center text-slate-400 text-xs">
                        Belum ada aktivitas presensi hari ini. Silakan klik tombol <strong>Punch</strong> di bawah.
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>

<!-- Slide-out Drawer Menu -->
<div id="drawer-backdrop" onclick="toggleDrawer()" class="fixed inset-0 bg-black/50 z-40 hidden transition-opacity"></div>
<div id="drawer-menu" class="fixed top-0 left-0 bottom-0 w-72 bg-white z-50 transform -translate-x-full transition-transform duration-300 shadow-2xl flex flex-col justify-between">
    <div>
        <!-- Drawer Header with Official HadirKU Logo -->
        <div class="bg-gradient-to-br from-emerald-600 to-emerald-700 text-white p-5">
            <div class="flex items-center gap-4 mb-4">
                <img src="{{ asset('images/logo.jpg') }}" alt="Logo HadirKU" class="w-12 h-12 rounded-2xl border-2 border-white/30 object-cover shadow-md flex-shrink-0">
                <div class="pl-0.5">
                    <h2 class="font-extrabold text-base tracking-tight text-white leading-none">HAdirKu</h2>
                    <p class="text-[9px] text-emerald-200 tracking-wider font-semibold uppercase mt-1">PRESENSI &bull; MUDAH &bull; TERPERCAYA</p>
                </div>
            </div>

            <div class="flex items-center gap-3.5 bg-black/15 p-2.5 rounded-2xl backdrop-blur-xs">
                <img src="{{ $user->avatar_url }}" alt="Avatar" class="w-10 h-10 rounded-full border border-white/40 object-cover">
                <div class="overflow-hidden">
                    <h3 class="font-bold text-xs text-white truncate">{{ $user->name }}</h3>
                    <p class="text-[10px] text-emerald-100 font-mono">{{ $user->nik ?? 'HK-PEG' }} &bull; {{ $user->position ?? 'Pegawai' }}</p>
                </div>
            </div>
        </div>

        <!-- Navigation Links -->
        <div class="p-4 space-y-1 text-sm font-medium text-slate-700">
            <a href="{{ route('user.dashboard') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl bg-emerald-50 text-emerald-700 font-bold">
                <i class="fa-solid fa-house w-5 text-center text-emerald-600"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('user.history') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 transition">
                <i class="fa-regular fa-clock w-5 text-center text-slate-400"></i>
                <span>Riwayat Kehadiran</span>
            </a>
            <a href="{{ route('user.leaves') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 transition">
                <i class="fa-solid fa-calendar-day w-5 text-center text-slate-400"></i>
                <span>Pengajuan Izin / Cuti</span>
            </a>
            <a href="{{ route('user.profile') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 transition">
                <i class="fa-regular fa-user w-5 text-center text-slate-400"></i>
                <span>Profil Pegawai</span>
            </a>

            <!-- Install App Quick Button -->
            <button type="button" onclick="installPwa()" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-xl text-emerald-700 hover:bg-emerald-50 transition text-left">
                <i class="fa-solid fa-download w-5 text-center text-emerald-600"></i>
                <span class="font-semibold">Pasang Aplikasi (PWA)</span>
            </button>

            @if($user->role === 'admin')
                <div class="pt-3 border-t border-slate-100 my-2"></div>
                <a href="/admin" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl bg-amber-50 text-amber-800 font-bold border border-amber-200">
                    <i class="fa-solid fa-shield-halved w-5 text-center text-amber-600"></i>
                    <span>Buka Panel Admin</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Drawer Footer -->
    <div class="p-4 border-t border-slate-100">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center space-x-2 py-2.5 px-4 bg-red-50 hover:bg-red-100 text-red-600 rounded-xl font-bold text-xs transition">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                <span>Keluar Akun (Logout)</span>
            </button>
        </form>
    </div>
</div>

<script>
    function toggleDrawer() {
        const drawer = document.getElementById('drawer-menu');
        const backdrop = document.getElementById('drawer-backdrop');
        const isClosed = drawer.classList.contains('-translate-x-full');
        if (isClosed) {
            drawer.classList.remove('-translate-x-full');
            backdrop.classList.remove('hidden');
        } else {
            drawer.classList.add('-translate-x-full');
            backdrop.classList.add('hidden');
        }
    }

    function showNotifications() {
        Swal.fire({
            title: 'Notifikasi',
            html: `
                <div class="text-left text-xs space-y-2">
                    <div class="p-2.5 bg-emerald-50 rounded-lg border border-emerald-100">
                        <strong class="text-emerald-800 block">Jadwal Kerja Aktif</strong>
                        <span class="text-slate-600">Shift Regular 08:00 - 17:00 WIB. Toleransi 15 menit.</span>
                    </div>
                    <div class="p-2.5 bg-blue-50 rounded-lg border border-blue-100">
                        <strong class="text-blue-800 block">Geofencing Aktif</strong>
                        <span class="text-slate-600">Presensi hanya berlaku dalam radius {{ $office->radius_meters ?? 100 }}m dari {{ $office->name ?? 'Kantor' }}.</span>
                    </div>
                    <div class="p-2.5 bg-purple-50 rounded-lg border border-purple-100">
                        <strong class="text-purple-800 block">Verifikasi Wajah Siap</strong>
                        <span class="text-slate-600">Arahkan kamera ke wajah saat menekan tombol Punch.</span>
                    </div>
                </div>
            `,
            confirmButtonText: 'Tutup',
            confirmButtonColor: '#059669',
        });
    }
</script>
@endsection
