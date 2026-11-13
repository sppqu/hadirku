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
                ->extraAttributes([
                    'class' => 'card-stat-pegawai bg-white dark:bg-gray-900 shadow-sm transition hover:shadow-md',
                    'style' => 'border: 1.5px solid #6366f1 !important; border-radius: 1rem !important;',
                ]),

            Stat::make('Hadir Hari Ini', "{$presentToday} / {$totalEmployees}")
                ->description("{$attendanceRate}% kehadiran")
                ->descriptionIcon('heroicon-m-check-badge')
                ->extraAttributes([
                    'class' => 'card-stat-hadir bg-white dark:bg-gray-900 shadow-sm transition hover:shadow-md',
                    'style' => 'border: 1.5px solid #10b981 !important; border-radius: 1rem !important;',
                ]),

            Stat::make('Terlambat Hari Ini', $lateToday)
                ->description('Melewati jam toleransi')
                ->descriptionIcon('heroicon-m-clock')
                ->extraAttributes([
                    'class' => 'card-stat-terlambat bg-white dark:bg-gray-900 shadow-sm transition hover:shadow-md',
                    'style' => 'border: 1.5px solid #f43f5e !important; border-radius: 1rem !important;',
                ]),

            Stat::make('Izin / Cuti Hari Ini', $leavesToday)
                ->description('Pengajuan disetujui')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->extraAttributes([
                    'class' => 'card-stat-izin bg-white dark:bg-gray-900 shadow-sm transition hover:shadow-md',
                    'style' => 'border: 1.5px solid #f59e0b !important; border-radius: 1rem !important;',
                ]),
        ];
    }
}
