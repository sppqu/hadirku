@extends('layouts.mobile', ['title' => 'Dashboard Kehadiran - HadirKU'])

@section('content')
<div class="relative bg-slate-50 min-h-full">
    <!-- Header Section (Green background with Greeting & Notification) -->
    <div class="bg-app-green text-white px-5 pt-5 pb-28 relative">
        <div class="flex items-center justify-between mb-4">
            <!-- App Name Branding (Menggantikan menu navigasi samping) -->
            <div class="flex items-center gap-2.5">
                <img src="{{ asset('images/logo.jpg') }}" alt="Logo HadirKU" class="w-8 h-8 rounded-xl border border-white/30 object-cover shadow-sm">
                <div>
                    <span class="font-extrabold text-base tracking-tight text-white leading-none block">HAdirKu</span>
                    <span class="text-[9px] text-emerald-200 tracking-wider font-semibold uppercase block mt-0.5">E-Presensi</span>
                </div>
            </div>

            <!-- Notification Bell with Badge -->
            <div class="relative">
                <button type="button" onclick="showNotifications()" class="text-white hover:text-emerald-100 p-2 rounded-xl bg-white/10 backdrop-blur-xs active:scale-95 transition">
                    <i class="fa-regular fa-bell text-lg"></i>
                </button>
                <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center border-2 border-app-green">3</span>
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

        @if(!empty($todayHoliday))
            <div class="bg-rose-50 border border-rose-200 rounded-2xl p-3.5 flex items-center gap-3 shadow-xs">
                <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-calendar-xmark text-sm"></i>
                </div>
                <div class="text-xs">
                    <div class="font-bold text-rose-900">Hari Libur: {{ $todayHoliday->name }}</div>
                    <p class="text-[11px] text-rose-700 mt-0.5">{{ $todayHoliday->type === 'national' ? 'Libur Nasional' : 'Libur Kantor / Cuti Bersama' }} &bull; Presensi masuk tidak diperlukan.</p>
                </div>
            </div>
        @elseif(isset($isWorkingDayToday) && ! $isWorkingDayToday)
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-3.5 flex items-center gap-3 shadow-xs">
                <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-calendar-day text-sm"></i>
                </div>
                <div class="text-xs">
                    <div class="font-bold text-amber-900">Di Luar Jadwal Hari Kerja</div>
                    <p class="text-[11px] text-amber-700 mt-0.5">Jadwal hari kerja Anda: <span class="font-semibold">{{ $user->working_days_summary }}</span>.</p>
                </div>
            </div>
        @endif

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

            @if($todayAttendance && $todayAttendance->status === 'pending')
                <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-2xl flex items-center justify-between text-xs text-amber-950">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-7 h-7 rounded-lg bg-amber-200/80 text-amber-800 flex items-center justify-center text-xs flex-shrink-0">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                        <div>
                            <div class="font-bold text-[11px] leading-tight">Presensi di Luar Radius</div>
                            <div class="text-[10px] text-amber-800/80 leading-tight mt-0.5">Status PENDING &bull; Menunggu persetujuan Admin</div>
                        </div>
                    </div>
                    <span class="text-[10px] font-extrabold bg-amber-500 text-white px-2.5 py-0.5 rounded-full shadow-xs">Pending</span>
                </div>
            @endif
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
                                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm flex-shrink-0">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
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

<script>
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
