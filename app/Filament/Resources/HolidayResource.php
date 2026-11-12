<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HolidayResource\Pages;
use App\Models\Holiday;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HolidayResource extends Resource
{
    protected static ?string $model = Holiday::class;

    protected static ?string $navigationGroup = 'Data Master';

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $modelLabel = 'Hari Libur';

    protected static ?string $pluralModelLabel = 'Hari Libur';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Hari Libur')
                    ->description('Atur tanggal libur nasional, cuti bersama, atau libur khusus kantor.')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Hari Libur')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Contoh: Hari Kemerdekaan RI / Cuti Bersama'),
                                Forms\Components\DatePicker::make('date')
                                    ->label('Tanggal Libur')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d M Y'),
                                Forms\Components\Select::make('type')
                                    ->label('Jenis Libur')
                                    ->options([
                                        'national' => 'Libur Nasional',
                                        'company' => 'Libur Kantor / Cuti Bersama',
                                    ])
                                    ->default('national')
                                    ->required(),
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Status Aktif')
                                    ->helperText('Hanya hari libur berstatus aktif yang berlaku di sistem presensi.')
                                    ->default(true),
                            ]),
                        Forms\Components\Textarea::make('description')
                            ->label('Keterangan')
                            ->rows(2)
                            ->placeholder('Catatan tambahan (opsional)')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('date', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->description(fn (Holiday $record): string => $record->date->locale('id')->translatedFormat('l'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Hari Libur')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'national' => 'Libur Nasional',
                        'company' => 'Libur Kantor',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'national' => 'danger',
                        'company' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Aktif'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis Libur')
                    ->options([
                        'national' => 'Libur Nasional',
                        'company' => 'Libur Kantor / Cuti Bersama',
                    ]),
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
            'index' => Pages\ListHolidays::route('/'),
            'create' => Pages\CreateHoliday::route('/create'),
            'edit' => Pages\EditHoliday::route('/{record}/edit'),
        ];
    }
}
