<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Handle new face reference upload from admin
        if (! empty($data['new_face_reference'])) {
            // Delete old face reference file if different
            if ($this->record->face_reference && Storage::disk('public')->exists($this->record->face_reference)) {
                Storage::disk('public')->delete($this->record->face_reference);
            }
            $data['face_reference'] = $data['new_face_reference'];
        }
        unset($data['new_face_reference']);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve_face')
                ->label('Setujui Wajah Pegawai')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (): bool => $this->record->face_reference_status === 'pending' || ! empty($this->record->pending_face_reference))
                ->modalHeading(fn () => "Verifikasi Pendaftaran Wajah - {$this->record->name}")
                ->modalSubheading('Bandingkan foto wajah yang diajukan dengan foto profil. Jika disetujui, pendaftaran wajah akan dikunci.')
                ->form([
                    Forms\Components\Placeholder::make('comparison')
                        ->label('Perbandingan Foto Identitas')
                        ->content(function () {
                            $pendingUrl = $this->record->pending_face_reference_url;
                            $activeUrl = $this->record->face_reference_url;
                            $avatarUrl = $this->record->avatar_url;

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
                ->action(function () {
                    $record = $this->record;
                    if ($record->face_reference && $record->face_reference !== $record->pending_face_reference && Storage::disk('public')->exists($record->face_reference)) {
                        Storage::disk('public')->delete($record->face_reference);
                    }
                    $record->face_reference = $record->pending_face_reference;
                    $record->pending_face_reference = null;
                    $record->face_reference_status = 'approved';
                    $record->allow_face_registration = false;
                    $record->face_unlock_requested = false;
                    $record->face_rejection_reason = null;
                    $record->save();

                    Notification::make()
                        ->title('Pendaftaran Wajah Disetujui')
                        ->body("Foto wajah pegawai {$record->name} berhasil diverifikasi dan izin registrasi telah dikunci.")
                        ->success()
                        ->send();

                    $this->fillForm();
                }),

            Actions\Action::make('reject_face')
                ->label('Tolak Wajah')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (): bool => $this->record->face_reference_status === 'pending' || ! empty($this->record->pending_face_reference))
                ->modalHeading(fn () => "Tolak Pendaftaran Wajah - {$this->record->name}")
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
                ->action(function (array $data) {
                    $record = $this->record;
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

                    $this->fillForm();
                }),

            Actions\Action::make('unlock_registration')
                ->label('Buka Kunci Reg Wajah')
                ->icon('heroicon-o-lock-open')
                ->color('info')
                ->visible(fn (): bool => ! $this->record->allow_face_registration && $this->record->face_reference_status === 'approved')
                ->requiresConfirmation()
                ->modalHeading('Buka Kunci Registrasi Wajah')
                ->modalDescription(fn () => "Apakah Anda yakin ingin membuka kunci izin pendaftaran wajah untuk {$this->record->name}? Pegawai akan dapat memindai ulang wajahnya melalui menu profil HP.")
                ->action(function () {
                    $this->record->allow_face_registration = true;
                    $this->record->face_unlock_requested = false;
                    $this->record->save();

                    Notification::make()
                        ->title('Izin Registrasi Dibuka')
                        ->body("Izin registrasi wajah dibuka untuk {$this->record->name}.")
                        ->success()
                        ->send();

                    $this->fillForm();
                }),

            Actions\DeleteAction::make()
                ->hidden(fn (): bool => $this->record->email === 'admin@hadirku.com' || $this->record->id === 1),
        ];
    }
}
