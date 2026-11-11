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

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Attendance::query()
                    ->whereDate('date', Carbon::today())
                    ->whereNotNull('check_in')
                    ->latest('check_in')
            )
            ->columns([
                Tables\Columns\ImageColumn::make('check_in_photo')
                    ->label('Foto Wajah')
                    ->circular()
                    ->defaultImageUrl(url('https://ui-avatars.com/api/?name=In&background=059669&color=fff')),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Pegawai')
                    ->weight('bold')
                    ->description(fn (Attendance $record): string => $record->user->position ?? '-'),
                Tables\Columns\TextColumn::make('user.department.name')
                    ->label('Departemen')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('check_in')
                    ->label('Jam Masuk')
                    ->time('H:i:s'),
                Tables\Columns\TextColumn::make('check_in_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'late' ? 'Terlambat' : 'Tepat Waktu')
                    ->color(fn (string $state): string => match ($state) {
                        'late' => 'danger',
                        'on_time' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('gps')
                    ->label('Jarak GPS')
                    ->state(fn (Attendance $record): string => "{$record->check_in_distance} m dari kantor")
                    ->badge()
                    ->color(fn (Attendance $record): string => ($record->check_in_distance > ($record->office->radius_meters ?? 100)) ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('check_out')
                    ->label('Jam Pulang')
                    ->time('H:i:s')
                    ->placeholder('Masih Bekerja...'),
            ]);
    }
}
