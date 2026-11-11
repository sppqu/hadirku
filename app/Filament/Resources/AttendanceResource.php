<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationGroup = 'Presensi & Kehadiran';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $modelLabel = 'Data Presensi';

    protected static ?string $pluralModelLabel = 'Data Presensi';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Pegawai')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\DatePicker::make('date')
                    ->label('Tanggal')
                    ->required()
                    ->default(now()),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TimePicker::make('check_in')
                            ->label('Jam Masuk')
                            ->seconds(false),
                        Forms\Components\TimePicker::make('check_out')
                            ->label('Jam Pulang')
                            ->seconds(false),
                        Forms\Components\Select::make('check_in_status')
                            ->label('Status Masuk')
                            ->options([
                                'on_time' => 'Tepat Waktu',
                                'late' => 'Terlambat',
                            ]),
                        Forms\Components\Select::make('status')
                            ->label('Status Kehadiran')
                            ->options([
                                'present' => 'Hadir',
                                'late' => 'Terlambat',
                                'half_day' => 'Setengah Hari',
                                'absent' => 'Alpha / Tidak Hadir',
                                'izin' => 'Izin',
                                'sakit' => 'Sakit',
                                'cuti' => 'Cuti',
                            ])
                            ->required(),
                    ]),
                Forms\Components\Textarea::make('notes')
                    ->label('Catatan Kehadiran')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\ImageColumn::make('check_in_photo')
                    ->label('Foto Masuk')
                    ->circular()
                    ->defaultImageUrl(url('https://ui-avatars.com/api/?name=In&background=059669&color=fff')),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Pegawai')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Attendance $record): string => $record->user->nik ?? '-'),
                Tables\Columns\TextColumn::make('user.department.name')
                    ->label('Departemen')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('check_in')
                    ->label('Masuk')
                    ->time('H:i')
                    ->badge()
                    ->color(fn (Attendance $record): string => $record->check_in_status === 'late' ? 'danger' : 'success')
                    ->description(fn (Attendance $record): string => $record->check_in_status === 'late' ? 'Terlambat' : 'Tepat Waktu'),
                Tables\Columns\TextColumn::make('check_out')
                    ->label('Pulang')
                    ->time('H:i')
                    ->placeholder('--:--')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('formatted_working_hours')
                    ->label('Jam Kerja')
                    ->badge()
                    ->color('primary')
                    ->suffix(' Jam'),
                Tables\Columns\TextColumn::make('gps_status')
                    ->label('GPS & Geofence')
                    ->state(function (Attendance $record): string {
                        if ($record->check_in_distance !== null) {
                            return "{$record->check_in_distance} m";
                        }
                        return 'Lokasi Valid';
                    })
                    ->badge()
                    ->color(fn (Attendance $record): string => ($record->check_in_distance > ($record->office->radius_meters ?? 100)) ? 'danger' : 'success')
                    ->icon('heroicon-m-map-pin'),
                Tables\Columns\IconColumn::make('face_verified')
                    ->label('Wajah Valid')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $q, $date) => $q->whereDate('date', '>=', $date))
                            ->when($data['until'], fn (Builder $q, $date) => $q->whereDate('date', '<=', $date));
                    }),
                Tables\Filters\SelectFilter::make('department')
                    ->relationship('user.department', 'name')
                    ->label('Departemen'),
                Tables\Filters\SelectFilter::make('check_in_status')
                    ->label('Status Masuk')
                    ->options([
                        'on_time' => 'Tepat Waktu',
                        'late' => 'Terlambat',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Ringkasan Kehadiran')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('user.name')->label('Pegawai'),
                                Infolists\Components\TextEntry::make('date')->label('Tanggal')->date('d F Y'),
                                Infolists\Components\TextEntry::make('user.department.name')->label('Departemen'),
                                Infolists\Components\TextEntry::make('check_in')->label('Jam Masuk')->time('H:i'),
                                Infolists\Components\TextEntry::make('check_out')->label('Jam Pulang')->time('H:i')->placeholder('--:--'),
                                Infolists\Components\TextEntry::make('formatted_working_hours')->label('Total Durasi Kerja'),
                            ]),
                    ]),
                Infolists\Components\Section::make('Verifikasi Lokasi GPS & Geofencing')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('office.name')->label('Kantor Tujuan'),
                                Infolists\Components\TextEntry::make('check_in_distance')
                                    ->label('Jarak Dari Titik Kantor')
                                    ->suffix(' Meter'),
                                Infolists\Components\TextEntry::make('coordinates')
                                    ->label('Koordinat Masuk')
                                    ->state(fn (Attendance $record): string => "{$record->check_in_lat}, {$record->check_in_lng}")
                                    ->url(fn (Attendance $record): string => "https://maps.google.com/?q={$record->check_in_lat},{$record->check_in_lng}", shouldOpenInNewTab: true)
                                    ->color('primary'),
                            ]),
                    ]),
                Infolists\Components\Section::make('Bukti Foto Wajah')
                    ->schema([
                        Infolists\Components\ImageEntry::make('check_in_photo')
                            ->label('Foto Wajah Saat Check In')
                            ->size(200),
                        Infolists\Components\ImageEntry::make('check_out_photo')
                            ->label('Foto Wajah Saat Check Out')
                            ->size(200),
                    ])->columns(2),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'view' => Pages\ViewAttendance::route('/{record}'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }
}
