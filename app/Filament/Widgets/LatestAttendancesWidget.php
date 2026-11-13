<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestAttendancesWidget extends BaseWidget
{
    protected static ?string $heading = 'Presensi Terbaru Hari Ini';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = [
        'default' => 'full',
        'md' => 1,
        'lg' => 1,
        'xl' => 1,
    ];

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Attendance::query()
                    ->with('user')
                    ->whereDate('date', Carbon::today())
                    ->whereNotNull('check_in')
                    ->latest('check_in')
            )
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\ImageColumn::make('profile_photo')
                    ->label('Foto Profil')
                    ->circular()
                    ->state(function (Attendance $record): string {
                        if ($record->user?->avatar) {
                            return asset('storage/' . $record->user->avatar);
                        }
                        if ($record->check_in_photo) {
                            return asset('storage/' . $record->check_in_photo);
                        }
                        return $record->user?->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($record->user?->name ?? 'Pegawai') . '&background=059669&color=fff';
                    }),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Pegawai')
                    ->weight('bold')
                    ->description(fn (Attendance $record): string => $record->user?->position ?? '-'),
                Tables\Columns\TextColumn::make('check_in')
                    ->label('Jam Masuk')
                    ->time('H:i:s'),
                Tables\Columns\TextColumn::make('check_in_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'late' => 'Terlambat',
                        'on_time' => 'Tepat Waktu',
                        default => $state ? ucfirst($state) : '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'late' => 'danger',
                        'on_time' => 'success',
                        default => 'gray',
                    }),
            ]);
    }
}
