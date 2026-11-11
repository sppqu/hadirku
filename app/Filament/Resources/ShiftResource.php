<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShiftResource\Pages;
use App\Models\Shift;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShiftResource extends Resource
{
    protected static ?string $model = Shift::class;

    protected static ?string $navigationGroup = 'Data Master';

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $modelLabel = 'Jam Kerja / Shift';

    protected static ?string $pluralModelLabel = 'Jam Kerja / Shift';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Konfigurasi Shift & Jam Kerja')
                    ->description('Tentukan jadwal masuk, pulang, toleransi keterlambatan, dan jam istirahat.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Shift')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Shift Regular (08:00 - 17:00)'),
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TimePicker::make('start_time')
                                    ->label('Jam Masuk')
                                    ->seconds(false)
                                    ->required()
                                    ->default('08:00'),
                                Forms\Components\TimePicker::make('end_time')
                                    ->label('Jam Pulang')
                                    ->seconds(false)
                                    ->required()
                                    ->default('17:00'),
                                Forms\Components\TextInput::make('grace_period_minutes')
                                    ->label('Toleransi Keterlambatan (Menit)')
                                    ->numeric()
                                    ->default(15)
                                    ->suffix('menit')
                                    ->helperText('Waktu toleransi sebelum dinyatakan Terlambat'),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TimePicker::make('break_start_time')
                                    ->label('Jam Mulai Istirahat')
                                    ->seconds(false)
                                    ->default('12:00'),
                                Forms\Components\TimePicker::make('break_end_time')
                                    ->label('Jam Selesai Istirahat')
                                    ->seconds(false)
                                    ->default('13:00'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Shift')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('working_hours')
                    ->label('Jam Kerja')
                    ->state(fn (Shift $record): string => substr($record->start_time, 0, 5) . ' - ' . substr($record->end_time, 0, 5))
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('break_hours')
                    ->label('Jam Istirahat')
                    ->state(fn (Shift $record): string => $record->break_start_time ? substr($record->break_start_time, 0, 5) . ' - ' . substr($record->break_end_time, 0, 5) : '-')
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('grace_period_minutes')
                    ->label('Toleransi')
                    ->formatStateUsing(fn ($state) => "{$state} menit")
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('users_count')
                    ->label('Pegawai')
                    ->counts('users')
                    ->badge()
                    ->color('gray'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShifts::route('/'),
            'create' => Pages\CreateShift::route('/create'),
            'edit' => Pages\EditShift::route('/{record}/edit'),
        ];
    }
}
