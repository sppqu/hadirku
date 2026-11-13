<?php

namespace App\Filament\Pages;

use App\Models\CampusProfile as CampusProfileModel;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

class CampusProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationGroup = 'Pengaturan';

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Profil Kampus';

    protected static ?string $title = 'Profil Kampus & Universitas';

    protected static ?string $slug = 'pengaturan/profil-kampus';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.campus-profile';

    public ?array $data = [];

    public function mount(): void
    {
        $profile = CampusProfileModel::current();
        $data = $profile->toArray();

        // Jangan isi state logo agar FilePond tidak me-load file lama/preview yang menyebabkan spinner macet
        unset($data['logo']);

        $this->form->fill($data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Identitas Perguruan Tinggi / Universitas')
                    ->description('Informasi utama institusi pendidikan tinggi')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                FileUpload::make('logo')
                                    ->label('Ganti Logo Kampus')
                                    ->disk('public')
                                    ->visibility('public')
                                    ->directory('campus')
                                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg', 'image/webp', 'image/svg+xml'])
                                    ->maxSize(2048)
                                    ->formatStateUsing(fn () => null)
                                    ->helperText('Pilih berkas baru (PNG, JPG, WebP, SVG, maks 2MB) jika ingin mengganti logo. Logo aktif sudah tampil di banner atas.')
                                    ->columnSpan(1),

                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Nama Universitas / Kampus')
                                            ->required()
                                            ->placeholder('Contoh: Universitas Indonesia')
                                            ->maxLength(255)
                                            ->columnSpanFull(),

                                        TextInput::make('foundation')
                                            ->label('Yayasan / Badan Hukum Penyelenggara (Opsional)')
                                            ->placeholder('Contoh: Yayasan Pembina Pendidikan Indonesia (Kosongkan jika PTN)')
                                            ->helperText('Jika diisi, nama yayasan ini akan menggantikan baris "KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI" pada Kop Laporan.')
                                            ->maxLength(255)
                                            ->columnSpanFull(),

                                        TextInput::make('campus_code')
                                            ->label('Kode PT (PDDikti)')
                                            ->placeholder('Contoh: 041001')
                                            ->maxLength(50),

                                        TextInput::make('rector_name')
                                            ->label('Nama Rektor / Pimpinan')
                                            ->placeholder('Contoh: Prof. Dr. ...')
                                            ->maxLength(255),

                                        Select::make('accreditation')
                                            ->label('Akreditasi Institusi (BAN-PT)')
                                            ->options([
                                                'Unggul' => 'Unggul',
                                                'A' => 'A (Amat Baik)',
                                                'Baik Sekali' => 'Baik Sekali',
                                                'B' => 'B (Baik)',
                                                'Baik' => 'Baik',
                                                'C' => 'C (Cukup)',
                                                'Belum Terakreditasi' => 'Belum Terakreditasi',
                                            ])
                                            ->searchable()
                                            ->placeholder('Pilih status akreditasi'),
                                    ])
                                    ->columnSpan(2),
                            ]),
                    ]),

                Section::make('Kontak & Komunikasi Resmi')
                    ->description('Saluran resmi universitas untuk civitas akademika, dosen, dan tendik')
                    ->icon('heroicon-o-phone')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('email')
                                    ->label('Email Resmi Kampus')
                                    ->email()
                                    ->placeholder('info@universitas.ac.id')
                                    ->maxLength(255),

                                TextInput::make('phone')
                                    ->label('Telepon / Helpdesk')
                                    ->tel()
                                    ->placeholder('(021) 7890123 / 0812...')
                                    ->maxLength(50),

                                TextInput::make('website')
                                    ->label('Website Resmi Kampus')
                                    ->url()
                                    ->placeholder('https://universitas.ac.id')
                                    ->maxLength(255),
                            ]),
                    ]),

                Section::make('Alamat & Kampus Pusat')
                    ->description('Lokasi rektorat dan gedung kampus utama')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Textarea::make('address')
                            ->label('Alamat Lengkap Kampus')
                            ->rows(3)
                            ->placeholder('Jl. Raya Kampus No. 1, Kelurahan...')
                            ->maxLength(500)
                            ->columnSpanFull(),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('city')
                                    ->label('Kota / Kabupaten')
                                    ->placeholder('Jakarta Selatan')
                                    ->maxLength(100),

                                TextInput::make('province')
                                    ->label('Provinsi')
                                    ->placeholder('DKI Jakarta')
                                    ->maxLength(100),

                                TextInput::make('postal_code')
                                    ->label('Kode Pos')
                                    ->placeholder('12345')
                                    ->maxLength(20),
                            ]),
                    ]),

                Section::make('Deskripsi, Visi & Misi Kampus')
                    ->description('Profil singkat, arah visi, dan misi akademik perguruan tinggi')
                    ->icon('heroicon-o-book-open')
                    ->collapsed()
                    ->schema([
                        Textarea::make('description')
                            ->label('Deskripsi Singkat Kampus')
                            ->rows(3)
                            ->placeholder('Deskripsi umum universitas, sejarah singkat, atau motto kampus...')
                            ->columnSpanFull(),

                        Textarea::make('vision')
                            ->label('Visi Kampus')
                            ->rows(3)
                            ->placeholder('Tuliskan visi jangka panjang universitas...')
                            ->columnSpanFull(),

                        Textarea::make('mission')
                            ->label('Misi Kampus')
                            ->rows(4)
                            ->placeholder("1. Menyelenggarakan pendidikan...\n2. Mengembangkan riset...")
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $profile = CampusProfileModel::current();

        // Jika user memilih file logo baru, simpan ke database
        if (!empty($data['logo'])) {
            $profile->logo = $data['logo'];
            $profile->save();
        }
        // Hapus key logo agar tidak menimpa logo yang sudah ada jika tidak ada berkas baru yang diupload
        unset($data['logo']);

        $profile->update($data);

        Notification::make()
            ->title('Berhasil Disimpan')
            ->body('Profil kampus berhasil diperbarui.')
            ->success()
            ->send();
    }
}
