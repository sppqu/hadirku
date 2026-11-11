<?php

namespace App\Filament\Exports;

use App\Models\User;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class UserExporter extends Exporter
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('nik')->label('NIK'),
            ExportColumn::make('name')->label('Nama Lengkap'),
            ExportColumn::make('email')->label('Email'),
            ExportColumn::make('phone')->label('Nomor HP/WA'),
            ExportColumn::make('role')->label('Role'),
            ExportColumn::make('department.name')->label('Departemen'),
            ExportColumn::make('position')->label('Jabatan'),
            ExportColumn::make('shift.name')->label('Shift Kerja'),
            ExportColumn::make('office.name')->label('Lokasi Kantor'),
            ExportColumn::make('is_active')->label('Status Aktif'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor data pegawai telah selesai. Sebanyak ' . number_format($export->successful_rows) . ' data telah diekspor.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' Terdapat ' . number_format($failedRowsCount) . ' baris yang gagal diekspor.';
        }

        return $body;
    }
}
