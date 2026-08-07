<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['country_code'] = $data['country_code'] ?? '+962';
        $data['phone'] = User::normalizePhoneNumber($data['phone'] ?? null);

        return $data;
    }
}
