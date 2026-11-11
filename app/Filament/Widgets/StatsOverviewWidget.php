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
                ->color('primary'),

            Stat::make('Hadir Hari Ini', "{$presentToday} / {$totalEmployees}")
                ->description("{$attendanceRate}% kehadiran")
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),

            Stat::make('Terlambat Hari Ini', $lateToday)
                ->description('Melewati jam toleransi')
                ->descriptionIcon('heroicon-m-clock')
                ->color($lateToday > 0 ? 'danger' : 'gray'),

            Stat::make('Izin / Cuti Hari Ini', $leavesToday)
                ->description('Pengajuan disetujui')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning'),
        ];
    }
}
