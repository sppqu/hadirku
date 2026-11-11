<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaveResource\Pages;
use App\Models\Leave;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LeaveResource extends Resource
{
    protected static ?string $model = Leave::class;

    protected static ?string $navigationGroup = 'Pengajuan & Persetujuan';

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $modelLabel = 'Izin & Cuti';

    protected static ?string $pluralModelLabel = 'Izin & Cuti';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Nama Pegawai')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('type')
                    ->label('Tipe Pengajuan')
                    ->options([
                        'izin' => 'Izin',
                        'sakit' => 'Sakit (Surat Dokter)',
                        'cuti' => 'Cuti Tahunan',
                    ])
                    ->required(),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Mulai Tanggal')
                            ->required(),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Sampai Tanggal')
                            ->required(),
                    ]),
                Forms\Components\Textarea::make('reason')
                    ->label('Alasan / Keterangan')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('attachment')
                    ->label('Lampiran Bukti (Surat Dokter / File)')
                    ->directory('leaves')
                    ->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->label('Status Pengajuan')
                    ->options([
                        'pending' => 'Menunggu Verifikasi (Pending)',
                        'approved' => 'Disetujui (Approved)',
                        'rejected' => 'Ditolak (Rejected)',
                    ])
                    ->default('pending')
                    ->required(),
                Forms\Components\Textarea::make('admin_notes')
                    ->label('Catatan Admin / Alasan Penolakan')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Pengajuan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pegawai')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn (Leave $record): string => $record->user->department->name ?? '-'),
                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn ($state) => strtoupper($state))
                    ->color(fn (string $state): string => match ($state) {
                        'izin' => 'info',
                        'sakit' => 'warning',
                        'cuti' => 'primary',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('duration')
                    ->label('Rentang Tanggal')
                    ->state(fn (Leave $record): string => date('d M', strtotime($record->start_date)) . ' s/d ' . date('d M Y', strtotime($record->end_date))),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Alasan')
                    ->limit(40),
                Tables\Columns\TextColumn::make('attachment')
                    ->label('Bukti')
                    ->formatStateUsing(fn ($state) => $state ? 'Lihat Bukti' : 'Tidak Ada')
                    ->url(fn (Leave $record) => $record->attachment_url, shouldOpenInNewTab: true)
                    ->color(fn ($record) => $record->attachment ? 'primary' : 'gray'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'izin' => 'Izin',
                        'sakit' => 'Sakit',
                        'cuti' => 'Cuti',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Leave $record): bool => $record->status === 'pending')
                    ->action(function (Leave $record) {
                        $record->update([
                            'status' => 'approved',
                            'approved_by' => auth()->id(),
                        ]);
                        Notification::make()
                            ->title('Pengajuan disetujui')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->visible(fn (Leave $record): bool => $record->status === 'pending')
                    ->action(function (Leave $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'approved_by' => auth()->id(),
                            'admin_notes' => $data['admin_notes'],
                        ]);
                        Notification::make()
                            ->title('Pengajuan ditolak')
                            ->warning()
                            ->send();
                    }),
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
            'index' => Pages\ListLeaves::route('/'),
            'create' => Pages\CreateLeave::route('/create'),
            'edit' => Pages\EditLeave::route('/{record}/edit'),
        ];
    }
}
