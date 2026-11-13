<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

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
                                Forms\Components\Section::make('Jadwal Hari Kerja Pegawai')
                                    ->description('Atur hari kerja aktif khusus untuk pegawai ini (misalnya hanya Senin, Rabu, Jumat).')
                                    ->schema([
                                        Forms\Components\CheckboxList::make('working_days')
                                            ->label('Hari Kerja Aktif')
                                            ->options(User::dayOptions())
                                            ->default([1, 2, 3, 4, 5])
                                            ->columns(7)
                                            ->required()
                                            ->helperText('Centang hari-hari di mana pegawai ini dijadwalkan masuk kerja.'),
                                    ]),
                            ]),
                        Forms\Components\Tabs\Tab::make('Foto & Verifikasi Wajah')
                            ->icon('heroicon-o-camera')
                            ->schema([
                                Forms\Components\Section::make('Verifikasi Pendaftaran Wajah')
                                    ->description('Persetujuan dan status verifikasi biometrik wajah pegawai')
                                    ->schema([
                                        Forms\Components\Placeholder::make('verification_info')
                                            ->label('')
                                            ->content(function (?User $record) {
                                                if (! $record) {
                                                    return null;
                                                }
                                                if ($record->face_reference_status === 'pending') {
                                                    return new HtmlString('
                                                        <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl flex items-start gap-3 text-amber-900">
                                                            <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center flex-shrink-0 text-sm font-bold">
                                                                ⏳
                                                            </div>
                                                            <div class="text-xs space-y-1">
                                                                <div class="font-bold text-amber-950 text-sm">Menunggu Persetujuan Admin</div>
                                                                <p>Pegawai ini telah mendaftarkan foto wajah mandiri dan sedang menunggu verifikasi admin. Pastikan foto wajah yang diajukan benar-benar milik pegawai tersebut sebelum disetujui.</p>
                                                            </div>
                                                        </div>
                                                    ');
                                                } elseif ($record->face_reference_status === 'approved') {
                                                    return new HtmlString('
                                                        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-start gap-3 text-emerald-900">
                                                            <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0 text-sm font-bold">
                                                                ✓
                                                            </div>
                                                            <div class="text-xs space-y-1">
                                                                <div class="font-bold text-emerald-950 text-sm">Wajah Terverifikasi (Terkunci)</div>
                                                                <p>Wajah pegawai telah disetujui dan saat ini registrasi berstatus terkunci untuk menjaga keamanan presensi.</p>
                                                            </div>
                                                        </div>
                                                    ');
                                                } elseif ($record->face_reference_status === 'rejected') {
                                                    return new HtmlString('
                                                        <div class="p-4 bg-red-50 border border-red-200 rounded-2xl flex items-start gap-3 text-red-900">
                                                            <div class="w-8 h-8 rounded-xl bg-red-100 text-red-700 flex items-center justify-center flex-shrink-0 text-sm font-bold">
                                                                ✕
                                                            </div>
                                                            <div class="text-xs space-y-1">
                                                                <div class="font-bold text-red-950 text-sm">Pendaftaran Wajah Ditolak</div>
                                                                <p>Alasan: ' . e($record->face_rejection_reason ?: 'Tidak memenuhi syarat.') . '</p>
                                                            </div>
                                                        </div>
                                                    ');
                                                }
                                                return null;
                                            })
                                            ->visible(fn (?User $record) => $record && $record->face_reference_status !== 'none')
                                            ->columnSpanFull(),

                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\Placeholder::make('pending_face_preview')
                                                    ->label('Foto Wajah Diajukan (Menunggu Persetujuan)')
                                                    ->content(function (?User $record) {
                                                        if (! $record || ! $record->pending_face_reference) {
                                                            return null;
                                                        }
                                                        $url = asset('storage/' . $record->pending_face_reference);
                                                        return new HtmlString('
                                                            <div class="space-y-2">
                                                                <div class="relative inline-block">
                                                                    <img src="' . $url . '" alt="Foto Wajah Diajukan" style="width: 180px; height: 200px; object-fit: cover; border-radius: 12px; border: 3px dashed #f59e0b; box-shadow: 0 4px 12px rgba(0,0,0,0.1);" />
                                                                    <span style="position: absolute; top: -8px; right: -8px; background: #f59e0b; color: white; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 20px;">PENDING</span>
                                                                </div>
                                                                <p class="text-xs text-gray-500">Foto yang baru saja dipindai/dikirimkan oleh pegawai dari profil HP.</p>
                                                            </div>
                                                        ');
                                                    })
                                                    ->visible(fn (?User $record) => ! empty($record?->pending_face_reference)),

                                                Forms\Components\Placeholder::make('face_reference_preview')
                                                    ->label('Foto Wajah Referensi Aktif (Terverifikasi)')
                                                    ->content(function (?User $record) {
                                                        if (! $record || ! $record->face_reference) {
                                                            return new HtmlString('
                                                                <div style="width: 180px; height: 200px; border-radius: 12px; border: 2px dashed #cbd5e1; display: flex; align-items: center; justify-content: center; background: #f8fafc;">
                                                                    <span style="font-size: 12px; color: #94a3b8; text-align: center;">Belum ada<br>foto referensi</span>
                                                                </div>
                                                            ');
                                                        }
                                                        $url = asset('storage/' . $record->face_reference);
                                                        return new HtmlString('
                                                            <div class="space-y-2">
                                                                <div class="relative inline-block">
                                                                    <img src="' . $url . '" alt="Foto Wajah Aktif" style="width: 180px; height: 200px; object-fit: cover; border-radius: 12px; border: 3px solid #10b981; box-shadow: 0 4px 12px rgba(0,0,0,0.1);" />
                                                                    <span style="position: absolute; top: -8px; right: -8px; background: #10b981; color: white; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 20px;">✓ AKTIF</span>
                                                                </div>
                                                                <p class="text-xs text-gray-500">Foto wajah resmi pembanding saat presensi biometrik.</p>
                                                            </div>
                                                        ');
                                                    })
                                                    ->visible(fn (?User $record) => $record !== null),

                                                Forms\Components\FileUpload::make('new_face_reference')
                                                    ->label('Upload / Ganti Foto Wajah Referensi')
                                                    ->disk('public')
                                                    ->visibility('public')
                                                    ->directory('faces')
                                                    ->image()
                                                    ->imageEditor()
                                                    ->helperText('Admin bisa upload manual foto wajah referensi baru di sini. Setelah disimpan, foto preview di atas akan diperbarui.')
                                                    ->columnSpanFull(),
                                            ]),

                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\Select::make('face_reference_status')
                                                    ->label('Status Approval Wajah')
                                                    ->options([
                                                        'none' => 'Belum Terdaftar',
                                                        'pending' => 'Menunggu Persetujuan Admin',
                                                        'approved' => 'Disetujui / Terverifikasi',
                                                        'rejected' => 'Ditolak',
                                                    ])
                                                    ->default('none')
                                                    ->required(),

                                                Forms\Components\Toggle::make('allow_face_registration')
                                                    ->label('Izinkan Registrasi Mandiri')
                                                    ->helperText('Jika aktif, pegawai dapat memindai wajah di HP. Terkunci otomatis setelah disetujui.')
                                                    ->default(false),

                                                Forms\Components\Toggle::make('face_unlock_requested')
                                                    ->label('Pengajuan Buka Kunci')
                                                    ->helperText('Pegawai meminta izin untuk registrasi ulang.')
                                                    ->disabled(),
                                            ]),

                                        Forms\Components\Textarea::make('face_rejection_reason')
                                            ->label('Alasan Penolakan (Catatan Admin)')
                                            ->placeholder('Tuliskan alasan penolakan jika pendaftaran tidak disetujui...')
                                            ->rows(2)
                                            ->columnSpanFull()
                                            ->visible(fn ($get) => $get('face_reference_status') === 'rejected' || ! empty($get('face_rejection_reason'))),
                                    ]),

                                Forms\Components\Section::make('Foto Profil & Status Akun')
                                    ->schema([
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\Placeholder::make('current_avatar')
                                                    ->label('Foto Profil Saat Ini')
                                                    ->content(function (?User $record) {
                                                        if (! $record) {
                                                            return new HtmlString('<span style="font-size: 12px; color: #94a3b8;">Belum ada foto profil</span>');
                                                        }
                                                        $url = $record->avatar_url;
                                                        return new HtmlString('
                                                            <div style="display: flex; align-items: center; gap: 12px; padding-top: 4px;">
                                                                <img src="' . $url . '" alt="Foto Profil" style="width: 52px; height: 52px; object-fit: cover; border-radius: 50%; border: 2px solid #cbd5e1; box-shadow: 0 2px 4px rgba(0,0,0,0.08);" />
                                                                <div>
                                                                    <div style="font-size: 12px; font-weight: 600; color: #334155;">' . e($record->name) . '</div>
                                                                    <div style="font-size: 11px; color: #64748b;">Foto aktif saat ini</div>
                                                                </div>
                                                            </div>
                                                        ');
                                                    })
                                                    ->visible(fn (?User $record) => $record !== null)
                                                    ->columnSpan(1),

                                                Forms\Components\FileUpload::make('avatar')
                                                    ->label('Ganti Foto Profil Pegawai')
                                                    ->disk('public')
                                                    ->visibility('public')
                                                    ->directory('avatars')
                                                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg', 'image/webp'])
                                                    ->maxSize(2048)
                                                    ->formatStateUsing(fn () => null)
                                                    ->dehydrated(fn ($state) => filled($state))
                                                    ->helperText('Pilih berkas baru jika ingin mengganti foto (PNG/JPG maks 2MB). Tanpa preview.')
                                                    ->columnSpan(fn (?User $record) => $record !== null ? 1 : 2),

                                                Forms\Components\Toggle::make('is_active')
                                                    ->label('Akun Pegawai Aktif')
                                                    ->helperText('Nonaktifkan jika pegawai sudah tidak bekerja di perusahaan.')
                                                    ->default(true)
                                                    ->columnSpan(1),
                                            ]),
                                    ]),
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
                Tables\Columns\TextColumn::make('working_days_summary')
                    ->label('Hari Kerja')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'pegawai' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\ImageColumn::make('face_reference')
                    ->label('Wajah Aktif')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl('https://ui-avatars.com/api/?name=No+Face&background=F1F5F9&color=94A3B8'),
                Tables\Columns\TextColumn::make('face_reference_status')
                    ->label('Status Wajah')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'approved' => 'Terverifikasi',
                        'pending' => 'Menunggu Approval',
                        'rejected' => 'Ditolak',
                        default => 'Belum Ada',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (?string $state): string => match ($state) {
                        'approved' => 'heroicon-m-check-badge',
                        'pending' => 'heroicon-m-clock',
                        'rejected' => 'heroicon-m-x-circle',
                        default => 'heroicon-m-minus-circle',
                    })
                    ->description(fn (User $record): ?string => $record->face_unlock_requested ? '⚠️ Minta Buka Kunci' : null),
                Tables\Columns\ToggleColumn::make('allow_face_registration')
                    ->label('Izin Reg Wajah'),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Aktif')
                    ->disabled(fn (?User $record): bool => $record?->id === auth()->id() || $record?->role === 'admin' || $record?->email === 'admin@hadirku.com'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'admin' => 'Admin',
                        'pegawai' => 'Pegawai',
                    ]),
                Tables\Filters\SelectFilter::make('face_reference_status')
                    ->label('Status Verifikasi Wajah')
                    ->options([
                        'pending' => '⏳ Menunggu Persetujuan Admin',
                        'approved' => '✓ Terverifikasi / Disetujui',
                        'rejected' => '✕ Ditolak',
                        'none' => 'Belum Mendaftar',
                    ]),
                Tables\Filters\SelectFilter::make('department')
                    ->relationship('department', 'name'),
            ])
            ->actions([
                Tables\Actions\Action::make('approve_face')
                    ->label('Setujui Wajah')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (User $record): bool => $record->face_reference_status === 'pending' || ! empty($record->pending_face_reference))
                    ->modalHeading(fn (User $record) => "Verifikasi Pendaftaran Wajah - {$record->name}")
                    ->modalSubheading('Bandingkan foto wajah yang diajukan dengan identitas pegawai. Jika disetujui, foto akan menjadi wajah referensi dan registrasi terkunci.')
                    ->form([
                        Forms\Components\Placeholder::make('comparison')
                            ->label('Perbandingan Foto Identitas')
                            ->content(function (User $record) {
                                $pendingUrl = $record->pending_face_reference_url;
                                $activeUrl = $record->face_reference_url;
                                $avatarUrl = $record->avatar_url;

                                $html = '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; text-align: center; margin-top: 8px;">';

                                $html .= '<div style="background: #fffbeb; border: 2px dashed #f59e0b; border-radius: 16px; padding: 12px;">';
                                $html .= '<span style="font-size: 11px; font-weight: 700; color: #b45309; text-transform: uppercase; display: block; margin-bottom: 8px;">📸 Foto Wajah Yang Diajukan</span>';
                                if ($pendingUrl) {
                                    $html .= '<img src="' . $pendingUrl . '" style="width: 140px; height: 160px; object-fit: cover; border-radius: 12px; margin: 0 auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1);" />';
                                } else {
                                    $html .= '<span style="font-size: 11px; color: #9ca3af;">Tidak ada gambar</span>';
                                }
                                $html .= '</div>';

                                $html .= '<div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 16px; padding: 12px;">';
                                $html .= '<span style="font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; display: block; margin-bottom: 8px;">👤 Foto Profil / Referensi Lama</span>';
                                $compareImg = $activeUrl ?: $avatarUrl;
                                $html .= '<img src="' . $compareImg . '" style="width: 140px; height: 160px; object-fit: cover; border-radius: 12px; margin: 0 auto; box-shadow: 0 4px 6px rgba(0,0,0,0.05);" />';
                                $html .= '</div>';

                                $html .= '</div>';
                                return new HtmlString($html);
                            })
                            ->columnSpanFull(),
                    ])
                    ->modalSubmitActionLabel('✓ Setujui & Kunci Registrasi')
                    ->action(function (User $record) {
                        if ($record->face_reference && $record->face_reference !== $record->pending_face_reference && Storage::disk('public')->exists($record->face_reference)) {
                            Storage::disk('public')->delete($record->face_reference);
                        }
                        $record->face_reference = $record->pending_face_reference;
                        $record->pending_face_reference = null;
                        $record->face_reference_status = 'approved';
                        $record->allow_face_registration = false; // Terkunci otomatis!
                        $record->face_unlock_requested = false;
                        $record->face_rejection_reason = null;
                        $record->save();

                        Notification::make()
                            ->title('Pendaftaran Wajah Disetujui')
                            ->body("Foto wajah pegawai {$record->name} berhasil diverifikasi dan registrasi wajah telah dikunci.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject_face')
                    ->label('Tolak Wajah')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (User $record): bool => $record->face_reference_status === 'pending' || ! empty($record->pending_face_reference))
                    ->modalHeading(fn (User $record) => "Tolak Pendaftaran Wajah - {$record->name}")
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->placeholder('Contoh: Foto buram, tidak menghadap lurus ke depan, atau bukan wajah pegawai bersangkutan.')
                            ->rows(3),
                        Forms\Components\Toggle::make('allow_retry')
                            ->label('Buka Izin Registrasi Ulang')
                            ->helperText('Jika aktif, pegawai dapat langsung mengambil foto wajah baru dari HP.')
                            ->default(true),
                    ])
                    ->modalSubmitActionLabel('✕ Konfirmasi Tolak')
                    ->action(function (User $record, array $data) {
                        if ($record->pending_face_reference && Storage::disk('public')->exists($record->pending_face_reference)) {
                            Storage::disk('public')->delete($record->pending_face_reference);
                        }
                        $record->pending_face_reference = null;
                        $record->face_reference_status = 'rejected';
                        $record->face_rejection_reason = $data['reason'];
                        $record->allow_face_registration = (bool) ($data['allow_retry'] ?? true);
                        $record->face_unlock_requested = false;
                        $record->save();

                        Notification::make()
                            ->title('Pendaftaran Wajah Ditolak')
                            ->body("Pengajuan wajah {$record->name} telah ditolak.")
                            ->warning()
                            ->send();
                    }),

                Tables\Actions\Action::make('unlock_registration')
                    ->label('Buka Kunci Reg')
                    ->icon('heroicon-o-lock-open')
                    ->color('info')
                    ->visible(fn (User $record): bool => ! $record->allow_face_registration && $record->face_reference_status === 'approved')
                    ->requiresConfirmation()
                    ->modalHeading('Buka Kunci Registrasi Wajah')
                    ->modalDescription(fn (User $record) => "Apakah Anda yakin ingin membuka kunci izin pendaftaran wajah untuk {$record->name}? Pegawai akan dapat memindai ulang wajahnya melalui menu profil HP.")
                    ->action(function (User $record) {
                        $record->allow_face_registration = true;
                        $record->face_unlock_requested = false;
                        $record->save();

                        Notification::make()
                            ->title('Izin Registrasi Dibuka')
                            ->body("Izin registrasi wajah dibuka untuk {$record->name}.")
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn (User $record): bool => $record->email === 'admin@hadirku.com' || $record->id === 1),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('enable_face_registration')
                        ->label('Aktifkan Izin Reg Wajah')
                        ->icon('heroicon-o-camera')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['allow_face_registration' => true]))
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('disable_face_registration')
                        ->label('Nonaktifkan Izin Reg Wajah')
                        ->icon('heroicon-o-x-mark')
                        ->color('warning')
                        ->action(fn ($records) => $records->each->update(['allow_face_registration' => false]))
                        ->requiresConfirmation(),
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

