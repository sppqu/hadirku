<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class AttendanceChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Kehadiran 7 Hari Terakhir';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = [
        'default' => 'full',
        'md' => 1,
        'lg' => 1,
        'xl' => 1,
    ];

    protected static ?string $maxHeight = '320px';

    protected function getData(): array
    {
        $dates = [];
        $presents = [];
        $lates = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dates[] = $date->translatedFormat('d M');

            $dateStr = $date->toDateString();
            $presentCount = Attendance::whereDate('date', $dateStr)
                ->whereNotNull('check_in')
                ->where('check_in_status', 'on_time')
                ->count();
            $lateCount = Attendance::whereDate('date', $dateStr)
                ->where('check_in_status', 'late')
                ->count();

            $presents[] = $presentCount;
            $lates[] = $lateCount;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Tepat Waktu',
                    'data' => $presents,
                    'backgroundColor' => '#10B981',
                    'borderColor' => '#059669',
                ],
                [
                    'label' => 'Terlambat',
                    'data' => $lates,
                    'backgroundColor' => '#EF4444',
                    'borderColor' => '#DC2626',
                ],
            ],
            'labels' => $dates,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
