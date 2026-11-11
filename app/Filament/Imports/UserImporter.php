<?php

namespace App\Filament\Imports;

use App\Models\Department;
use App\Models\Office;
use App\Models\Shift;
use App\Models\User;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Hash;

class UserImporter extends Importer
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('nik')
                ->label('NIK')
                ->requiredMapping()
                ->rules(['required', 'max:50']),

            ImportColumn::make('name')
                ->label('Nama Lengkap')
                ->requiredMapping()
                ->rules(['required', 'max:255']),

            ImportColumn::make('email')
                ->label('Email')
                ->requiredMapping()
                ->rules(['required', 'email', 'max:255']),

            ImportColumn::make('phone')
                ->label('Nomor HP/WA')
                ->rules(['nullable', 'max:30']),

            ImportColumn::make('password')
                ->label('Kata Sandi (Opsional, Default: password)')
                ->rules(['nullable', 'max:255']),

            ImportColumn::make('role')
                ->label('Role (admin/pegawai)')
                ->rules(['nullable', 'in:admin,pegawai']),

            ImportColumn::make('department')
                ->label('Nama Departemen')
                ->relationship(resolveUsing: 'name'),

            ImportColumn::make('position')
                ->label('Jabatan')
                ->rules(['nullable', 'max:100']),

            ImportColumn::make('shift')
                ->label('Nama Shift')
                ->relationship(resolveUsing: 'name'),

            ImportColumn::make('office')
                ->label('Nama Lokasi Kantor')
                ->relationship(resolveUsing: 'name'),

            ImportColumn::make('is_active')
                ->label('Status Aktif (1/0/true/false)')
                ->boolean()
                ->rules(['nullable', 'boolean']),
        ];
    }

    public function resolveRecord(): ?User
    {
        $user = null;

        if (!empty($this->data['nik'])) {
            $user = User::where('nik', $this->data['nik'])->first();
        }

        if (!$user && !empty($this->data['email'])) {
            $user = User::where('email', $this->data['email'])->first();
        }

        if (!$user) {
            $user = new User();
        }

        // Set default role if empty
        if (empty($this->data['role'])) {
            $user->role = 'pegawai';
        }

        // Set default password if empty
        if (empty($this->data['password'])) {
            if (!$user->exists) {
                $user->password = Hash::make('password');
            }
        } else {
            $user->password = Hash::make($this->data['password']);
        }

        // Default office if not provided
        if (empty($this->data['office']) && !$user->office_id) {
            $defaultOffice = Office::where('is_active', true)->first();
            if ($defaultOffice) {
                $user->office_id = $defaultOffice->id;
            }
        }

        // Default shift if not provided
        if (empty($this->data['shift']) && !$user->shift_id) {
            $defaultShift = Shift::first();
            if ($defaultShift) {
                $user->shift_id = $defaultShift->id;
            }
        }

        // Default is_active
        if (!isset($this->data['is_active'])) {
            $user->is_active = true;
        }

        return $user;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Impor data pegawai telah selesai. Sebanyak ' . number_format($import->successful_rows) . ' data berhasil diimpor.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' Terdapat ' . number_format($failedRowsCount) . ' baris yang gagal diimpor.';
        }

        return $body;
    }
}
