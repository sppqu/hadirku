<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $title = 'Profil Admin';

    protected static ?string $slug = 'profile';

    protected static string $view = 'filament.pages.admin-profile';

    public ?array $profileData = [];

    public ?array $passwordData = [];

    public function mount(): void
    {
        $user = Auth::user();

        $this->profileForm->fill([
            'avatar' => $user->avatar,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
        ]);

        $this->passwordForm->fill();
    }

    protected function getForms(): array
    {
        return [
            'profileForm',
            'passwordForm',
        ];
    }

    public function profileForm(Form $form): Form
    {
        $user = Auth::user();

        return $form
            ->schema([
                Section::make('Informasi Profil')
                    ->description('Perbarui nama, alamat email, nomor HP, dan foto profil akun admin Anda.')
                    ->schema([
                        FileUpload::make('avatar')
                            ->label('Foto Profil')
                            ->disk('public')
                            ->visibility('public')
                            ->directory('avatars')
                            ->image()
                            ->avatar(),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Lengkap')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->label('Alamat Email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->rules([
                                        Rule::unique('users', 'email')->ignore($user->id),
                                    ]),
                                TextInput::make('phone')
                                    ->label('Nomor HP / WhatsApp')
                                    ->tel()
                                    ->maxLength(30),
                            ]),
                    ]),
            ])
            ->statePath('profileData');
    }

    public function passwordForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Ubah Kata Sandi')
                    ->description('Pastikan akun Anda menggunakan kata sandi yang kuat dan minimal 8 karakter.')
                    ->schema([
                        TextInput::make('current_password')
                            ->label('Kata Sandi Saat Ini')
                            ->password()
                            ->revealable()
                            ->required()
                            ->rules([
                                fn () => function (string $attribute, $value, \Closure $fail) {
                                    if (! Hash::check($value, Auth::user()->password)) {
                                        $fail('Kata sandi saat ini tidak sesuai.');
                                    }
                                },
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('new_password')
                                    ->label('Kata Sandi Baru')
                                    ->password()
                                    ->revealable()
                                    ->required()
                                    ->minLength(8)
                                    ->different('current_password')
                                    ->same('new_password_confirmation'),
                                TextInput::make('new_password_confirmation')
                                    ->label('Konfirmasi Kata Sandi Baru')
                                    ->password()
                                    ->revealable()
                                    ->required(),
                            ]),
                    ]),
            ])
            ->statePath('passwordData');
    }

    public function updateProfile(): void
    {
        $data = $this->profileForm->getState();

        $user = Auth::user();
        $user->update([
            'avatar' => $data['avatar'] ?? null,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
        ]);

        Notification::make()
            ->title('Profil berhasil diperbarui')
            ->success()
            ->send();
    }

    public function updatePassword(): void
    {
        $data = $this->passwordForm->getState();

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($data['new_password']),
        ]);

        $this->passwordForm->fill();

        Notification::make()
            ->title('Kata sandi berhasil diubah')
            ->body('Silakan gunakan kata sandi baru Anda saat login berikutnya.')
            ->success()
            ->send();
    }
}
