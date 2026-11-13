<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = Carbon::today()->toDateString();
        $totalEmployees = User::where('role', 'pegawai')->where('is_active', true)->count();
        $presentToday = Attendance::whereDate('date', $today)->whereNotNull('check_in')->count();
        $lateToday = Attendance::whereDate('date', $today)->where('check_in_status', 'late')->count();
        $leavesToday = Leave::where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->count();

        $attendanceRate = $totalEmployees > 0 ? round(($presentToday / $totalEmployees) * 100) : 0;

        return [
            Stat::make('Total Pegawai', $totalEmployees)
                ->description('Pegawai aktif terdaftar')
                ->descriptionIcon('heroicon-m-users')
                ->color('info')
                ->extraAttributes([
                    'class' => 'border-2 border-blue-500/30 dark:border-blue-500/30 border-l-4 border-l-blue-500 rounded-xl shadow-sm',
                ]),

            Stat::make('Hadir Hari Ini', "{$presentToday} / {$totalEmployees}")
                ->description("{$attendanceRate}% kehadiran")
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->extraAttributes([
                    'class' => 'border-2 border-emerald-500/30 dark:border-emerald-500/30 border-l-4 border-l-emerald-500 rounded-xl shadow-sm',
                ]),

            Stat::make('Terlambat Hari Ini', $lateToday)
                ->description('Melewati jam toleransi')
                ->descriptionIcon('heroicon-m-clock')
                ->color($lateToday > 0 ? 'danger' : 'gray')
                ->extraAttributes([
                    'class' => 'border-2 border-rose-500/30 dark:border-rose-500/30 border-l-4 border-l-rose-500 rounded-xl shadow-sm',
                ]),

            Stat::make('Izin / Cuti Hari Ini', $leavesToday)
                ->description('Pengajuan disetujui')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning')
                ->extraAttributes([
                    'class' => 'border-2 border-amber-500/30 dark:border-amber-500/30 border-l-4 border-l-amber-500 rounded-xl shadow-sm',
                ]),
        ];
    }
}
