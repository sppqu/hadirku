<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationGroup = 'Data Master';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $modelLabel = 'Data Pegawai';

    protected static ?string $pluralModelLabel = 'Data Pegawai';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('UserTabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Data Pribadi & Akun')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('nik')
                                            ->label('Nomor Induk Karyawan (NIK)')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(50)
                                            ->placeholder('HK-2024001'),
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nama Lengkap')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('John Doe'),
                                        Forms\Components\TextInput::make('email')
                                            ->label('Alamat Email')
                                            ->email()
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255)
                                            ->placeholder('john@hadirku.com'),
                                        Forms\Components\TextInput::make('phone')
                                            ->label('Nomor WhatsApp / HP')
                                            ->tel()
                                            ->maxLength(30)
                                            ->placeholder('081234567890'),
                                        Forms\Components\Select::make('role')
                                            ->label('Peran Akses (Role)')
                                            ->options([
                                                'admin' => 'Admin (Panel Admin)',
                                                'pegawai' => 'Pegawai (Mobile User)',
                                            ])
                                            ->default('pegawai')
                                            ->required(),
                                        Forms\Components\TextInput::make('password')
                                            ->label('Kata Sandi')
                                            ->password()
                                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                                            ->dehydrated(fn ($state) => filled($state))
                                            ->required(fn (string $context): bool => $context === 'create')
                                            ->placeholder('Kosongkan jika tidak ingin mengubah sandi'),
                                    ]),
                            ]),
                        Forms\Components\Tabs\Tab::make('Penempatan & Jam Kerja')
                            ->icon('heroicon-o-briefcase')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Select::make('department_id')
                                            ->label('Departemen / Divisi')
                                            ->relationship('department', 'name')
                                            ->searchable()
                                            ->preload(),
                                        Forms\Components\TextInput::make('position')
                                            ->label('Jabatan')
                                            ->placeholder('Contoh: Software Engineer'),
                                        Forms\Components\Select::make('office_id')
                                            ->label('Lokasi Kantor Presensi')
                                            ->relationship('office', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->helperText('Lokasi titik koordinat GPS yang diizinkan untuk presensi'),
                                        Forms\Components\Select::make('shift_id')
                                            ->label('Shift Kerja')
                                            ->relationship('shift', 'name')
                                            ->searchable()
                                            ->preload(),
                                    ]),
                            ]),
                        Forms\Components\Tabs\Tab::make('Foto & Verifikasi Wajah')
                            ->icon('heroicon-o-camera')
                            ->schema([
                                Forms\Components\FileUpload::make('face_reference')
                                    ->label('Foto Wajah Referensi (Untuk Verifikasi Wajah)')
                                    ->image()
                                    ->directory('faces')
                                    ->imageEditor()
                                    ->helperText('Unggah foto wajah tampak depan yang jelas sebagai pembanding pada saat presensi wajah.'),
                                Forms\Components\FileUpload::make('avatar')
                                    ->label('Foto Profil')
                                    ->image()
                                    ->avatar()
                                    ->directory('avatars'),
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Akun Aktif')
                                    ->default(true),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar_url')
                    ->label('Foto')
                    ->circular(),
                Tables\Columns\TextColumn::make('nik')
                    ->label('NIK')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Pegawai')
                    ->searchable()
                    ->sortable()
                    ->description(fn (User $record): string => $record->email),
                Tables\Columns\TextColumn::make('department.name')
                    ->label('Departemen')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                Tables\Columns\TextColumn::make('position')
                    ->label('Jabatan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'pegawai' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('face_registered')
                    ->label('Verifikasi Wajah')
                    ->state(fn (User $record): bool => !empty($record->face_reference))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Aktif'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'admin' => 'Admin',
                        'pegawai' => 'Pegawai',
                    ]),
                Tables\Filters\SelectFilter::make('department')
                    ->relationship('department', 'name'),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
