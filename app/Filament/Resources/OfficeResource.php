<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OfficeResource\Pages;
use App\Models\Office;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OfficeResource extends Resource
{
    protected static ?string $model = Office::class;

    protected static ?string $navigationGroup = 'Data Master';

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $modelLabel = 'Lokasi Kantor';

    protected static ?string $pluralModelLabel = 'Lokasi Kantor';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Lokasi & Geofencing')
                    ->description('Tentukan koordinat kantor dan radius presensi (geofencing) dalam meter.')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Kantor / Titik Lokasi')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Kantor Pusat HadirKU'),
                        Forms\Components\Textarea::make('address')
                            ->label('Alamat Lengkap')
                            ->rows(2)
                            ->columnSpanFull(),
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('latitude')
                                    ->label('Latitude GPS')
                                    ->required()
                                    ->numeric()
                                    ->helperText('Contoh: -6.225014 (dapat disalin dari Google Maps)'),
                                Forms\Components\TextInput::make('longitude')
                                    ->label('Longitude GPS')
                                    ->required()
                                    ->numeric()
                                    ->helperText('Contoh: 106.809784'),
                                Forms\Components\TextInput::make('radius_meters')
                                    ->label('Radius Geofencing (Meter)')
                                    ->required()
                                    ->numeric()
                                    ->default(100)
                                    ->suffix('meter')
                                    ->helperText('Jarak maksimal pegawai dapat melakukan presensi'),
                            ]),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Lokasi Aktif')
                            ->default(true)
                            ->helperText('Hanya lokasi aktif yang dapat digunakan untuk presensi'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Kantor')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn (Office $record): string => $record->address ?: '-'),
                Tables\Columns\TextColumn::make('coordinates')
                    ->label('Koordinat GPS')
                    ->state(fn (Office $record): string => "{$record->latitude}, {$record->longitude}")
                    ->icon('heroicon-m-map-pin')
                    ->color('primary')
                    ->url(fn (Office $record): string => "https://maps.google.com/?q={$record->latitude},{$record->longitude}", shouldOpenInNewTab: true),
                Tables\Columns\TextColumn::make('radius_meters')
                    ->label('Radius')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn ($state) => "{$state} meter"),
                Tables\Columns\TextColumn::make('users_count')
                    ->label('Pegawai')
                    ->counts('users')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Status Aktif'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListOffices::route('/'),
            'create' => Pages\CreateOffice::route('/create'),
            'edit' => Pages\EditOffice::route('/{record}/edit'),
        ];
    }
}
