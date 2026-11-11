<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Exports\UserExporter;
use App\Filament\Imports\UserImporter;
use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadTemplate')
                ->label('Download Template CSV')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->url(asset('templates/template_import_pegawai.csv'))
                ->openUrlInNewTab(),

            ImportAction::make()
                ->importer(UserImporter::class)
                ->label('Import Pegawai (CSV/Excel)')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('info')
                ->modalHeading('Import Data Pegawai')
                ->modalDescription('Unggah berkas CSV atau Excel berisi data pegawai. Jika NIK atau Email sudah terdaftar, data pegawai akan diperbarui secara otomatis.'),

            ExportAction::make()
                ->exporter(UserExporter::class)
                ->label('Export Pegawai')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success'),

            Actions\CreateAction::make()
                ->label('Tambah Pegawai')
                ->icon('heroicon-o-plus'),
        ];
    }
}
