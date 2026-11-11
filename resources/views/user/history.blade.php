@extends('layouts.mobile', ['title' => 'Riwayat Kehadiran - HadirKU'])

@section('content')
<div class="relative bg-slate-50 min-h-full">
    <!-- Header Section (Green curved background with Back & Title & Filter) -->
    <div class="bg-app-green text-white px-5 pt-4 pb-8 rounded-b-[36px] shadow-sm relative">
        <div class="flex items-center justify-between mb-2">
            <a href="{{ route('user.dashboard') }}" class="text-white hover:text-emerald-100 p-1.5 -ml-1.5 rounded-lg active:scale-95 transition">
                <i class="fa-solid fa-arrow-left text-lg"></i>
            </a>

            <h1 class="text-base font-bold tracking-tight">Attendance History</h1>

            <button type="button" onclick="showFilterDialog()" class="text-white hover:text-emerald-100 p-1.5 rounded-lg active:scale-95 transition">
                <i class="fa-solid fa-arrow-up-wide-short text-lg"></i>
            </button>
        </div>
    </div>

    <!-- Main Container -->
    <div class="px-5 -mt-4 relative z-10 space-y-4">

        <!-- Calendar Navigator Card -->
        <div class="bg-white rounded-3xl p-5 shadow-[0_4px_20px_rgba(0,0,0,0.04)] border border-slate-100">
            <!-- Month Picker Navigation -->
            @php
                $prevMonth = $currentMonthDate->copy()->subMonth();
                $nextMonth = $currentMonthDate->copy()->addMonth();
            @endphp
            <div class="flex items-center justify-between mb-4 px-2">
                <a href="{{ route('user.history', ['month' => $prevMonth->month, 'year' => $prevMonth->year, 'date' => $prevMonth->toDateString()]) }}" class="text-slate-400 hover:text-slate-700 p-1">
                    <i class="fa-solid fa-chevron-left text-xs"></i>
                </a>
                <span class="text-sm font-bold text-slate-800">{{ $currentMonthDate->translatedFormat('F Y') }}</span>
                <a href="{{ route('user.history', ['month' => $nextMonth->month, 'year' => $nextMonth->year, 'date' => $nextMonth->toDateString()]) }}" class="text-slate-400 hover:text-slate-700 p-1">
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                </a>
            </div>

            <!-- Days of Week Header -->
            <div class="grid grid-cols-7 gap-1 text-center mb-2">
                @foreach(['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'] as $dayName)
                    <span class="text-[10px] font-semibold text-slate-400 tracking-wider">{{ $dayName }}</span>
                @endforeach
            </div>

            <!-- Calendar Days Grid (Horizontal scrollable or neat row) -->
            <div class="grid grid-cols-7 gap-1 text-center">
                @php
                    $startDayOfWeek = $currentMonthDate->copy()->startOfMonth()->dayOfWeekIso; // 1 (Mon) - 7 (Sun)
                @endphp

                {{-- Empty days before 1st of month --}}
                @for($b = 1; $b < $startDayOfWeek; $b++)
                    <div class="py-2"></div>
                @endfor

                @foreach($calendarDays as $cal)
                    <a 
                        href="{{ route('user.history', ['month' => $currentMonthDate->month, 'year' => $currentMonthDate->year, 'date' => $cal['date_str']]) }}" 
                        class="flex flex-col items-center py-1.5 rounded-2xl transition {{ $cal['is_selected'] ? 'bg-app-green text-white font-bold shadow-md shadow-emerald-600/30' : 'hover:bg-slate-50 text-slate-700' }}"
                    >
                        <span class="text-xs font-bold leading-none">{{ $cal['day_num'] }}</span>
                        
                        <!-- Status Dot -->
                        <div class="mt-1">
                            @if($cal['is_selected'])
                                <span class="w-1.5 h-1.5 rounded-full bg-white block"></span>
                            @elseif($cal['status'] === 'on_time')
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 block"></span>
                            @elseif($cal['status'] === 'late')
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 block"></span>
                            @elseif($cal['status'] === 'absent')
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500 block"></span>
                            @else
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-200 block"></span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Monthly Summary Pills (Present, Absent, Half Day, Total Hours) -->
        <div class="bg-white rounded-3xl p-4 border border-slate-100 shadow-sm">
            <div class="grid grid-cols-4 gap-2 text-center">
                <div class="p-1">
                    <span class="text-lg font-black text-emerald-600 block leading-tight">{{ $presentCount }}</span>
                    <span class="text-[10px] font-semibold text-slate-400 block mt-0.5">Present</span>
                </div>
                <div class="p-1">
                    <span class="text-lg font-black text-red-500 block leading-tight">2</span>
                    <span class="text-[10px] font-semibold text-slate-400 block mt-0.5">Absent</span>
                </div>
                <div class="p-1">
                    <span class="text-lg font-black text-amber-500 block leading-tight">{{ $lateCount ?: 1 }}</span>
                    <span class="text-[10px] font-semibold text-slate-400 block mt-0.5">Half Day</span>
                </div>
                <div class="p-1">
                    <span class="text-lg font-black text-blue-600 block leading-tight">{{ $totalHours }}</span>
                    <span class="text-[10px] font-semibold text-slate-400 block mt-0.5">Total Hours</span>
                </div>
            </div>
        </div>

        <!-- Selected Day Detailed Activity Timeline (Matches Screenshot 2) -->
        <div class="bg-white rounded-3xl p-5 border border-slate-100 shadow-sm space-y-4">
            <!-- Day Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-xs font-bold text-slate-800">{{ $selectedCarbon->translatedFormat('l, d F Y') }}</h3>
                <span class="text-xs font-bold text-app-green">
                    Total Hours: {{ $selectedAttendance ? $selectedAttendance->formatted_working_hours : '00:00' }}
                </span>
            </div>

            <!-- Timeline Elements -->
            <div class="relative pl-6 space-y-5 before:absolute before:left-2 before:top-3 before:bottom-3 before:w-0.5 before:bg-slate-200">
                
                <!-- Check In Item -->
                <div class="relative flex items-center justify-between">
                    <div class="absolute -left-6 w-4 h-4 rounded-full bg-emerald-500 border-2 border-white shadow-sm flex items-center justify-center"></div>
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-arrow-right-to-bracket"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-800">Check In</h4>
                            <div class="flex items-center space-x-2 text-[11px] text-slate-500">
                                <span class="font-semibold">{{ $selectedAttendance && $selectedAttendance->check_in ? date('h:i A', strtotime($selectedAttendance->check_in)) : '--:-- --' }}</span>
                                <span class="text-slate-300">&bull;</span>
                                <span class="text-slate-400 flex items-center gap-0.5">
                                    <i class="fa-solid fa-location-dot text-emerald-500 text-[10px]"></i> Office
                                </span>
                            </div>
                        </div>
                    </div>
                    <div>
                        @if($selectedAttendance && $selectedAttendance->check_in)
                            <span class="text-[11px] font-bold {{ $selectedAttendance->check_in_status === 'late' ? 'text-amber-500' : 'text-emerald-600' }}">
                                {{ $selectedAttendance->check_in_status === 'late' ? 'Late' : 'On time' }}
                            </span>
                        @else
                            <span class="text-[11px] font-semibold text-slate-400">Not recorded</span>
                        @endif
                    </div>
                </div>

                <!-- Break Start Item -->
                <div class="relative flex items-center justify-between">
                    <div class="absolute -left-6 w-4 h-4 rounded-full bg-amber-400 border-2 border-white shadow-sm"></div>
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-mug-saucer"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-800">Break Start</h4>
                            <div class="flex items-center space-x-2 text-[11px] text-slate-500">
                                <span class="font-semibold">{{ $selectedAttendance && $selectedAttendance->break_start ? date('h:i A', strtotime($selectedAttendance->break_start)) : '01:15 PM' }}</span>
                                <span class="text-slate-300">&bull;</span>
                                <span class="text-slate-400 flex items-center gap-0.5">
                                    <i class="fa-solid fa-location-dot text-emerald-500 text-[10px]"></i> Office
                                </span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <span class="text-[11px] font-medium text-slate-500">
                            Duration <strong class="text-slate-700">00:45</strong>
                        </span>
                    </div>
                </div>

                <!-- Break End Item -->
                <div class="relative flex items-center justify-between">
                    <div class="absolute -left-6 w-4 h-4 rounded-full bg-amber-400 border-2 border-white shadow-sm"></div>
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-mug-saucer"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-800">Break End</h4>
                            <div class="flex items-center space-x-2 text-[11px] text-slate-500">
                                <span class="font-semibold">{{ $selectedAttendance && $selectedAttendance->break_end ? date('h:i A', strtotime($selectedAttendance->break_end)) : '02:00 PM' }}</span>
                                <span class="text-slate-300">&bull;</span>
                                <span class="text-slate-400 flex items-center gap-0.5">
                                    <i class="fa-solid fa-location-dot text-emerald-500 text-[10px]"></i> Office
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Check Out Item -->
                <div class="relative flex items-center justify-between">
                    <div class="absolute -left-6 w-4 h-4 rounded-full {{ $selectedAttendance && $selectedAttendance->check_out ? 'bg-emerald-500' : 'bg-red-400' }} border-2 border-white shadow-sm"></div>
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-xl bg-red-50 text-red-500 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-800">Check Out</h4>
                            <div class="flex items-center space-x-2 text-[11px] text-slate-500">
                                <span class="font-semibold">{{ $selectedAttendance && $selectedAttendance->check_out ? date('h:i A', strtotime($selectedAttendance->check_out)) : '--:-- --' }}</span>
                                <span class="text-slate-300">&bull;</span>
                                <span class="text-slate-400 flex items-center gap-0.5">
                                    <i class="fa-solid fa-location-dot text-emerald-500 text-[10px]"></i> Office
                                </span>
                            </div>
                        </div>
                    </div>
                    <div>
                        @if($selectedAttendance && $selectedAttendance->check_out)
                            <span class="text-[11px] font-bold text-emerald-600">Completed</span>
                        @else
                            <span class="text-[11px] font-bold text-amber-500">Pending</span>
                        @endif
                    </div>
                </div>

            </div>

            <!-- Footer Company Time Zone note -->
            <p class="text-[10px] text-slate-400 italic pt-2">
                * Times shown are as per your company's time zone (Asia/Jakarta WIB).
            </p>
        </div>

    </div>
</div>

<script>
    function showFilterDialog() {
        Swal.fire({
            title: 'Filter Riwayat',
            html: `
                <div class="text-left text-xs space-y-3">
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Status Kehadiran</label>
                        <select id="swal-status" class="w-full p-2 border rounded-lg">
                            <option value="">Semua Status</option>
                            <option value="on_time">Tepat Waktu</option>
                            <option value="late">Terlambat</option>
                            <option value="absent">Tidak Hadir</option>
                        </select>
                    </div>
                </div>
            `,
            confirmButtonText: 'Terapkan',
            confirmButtonColor: '#059669',
        });
    }
</script>
@endsection
