<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Handle new face reference upload
        if (! empty($data['new_face_reference'])) {
            $data['face_reference'] = $data['new_face_reference'];
        }
        unset($data['new_face_reference']);

        return $data;
    }
}
