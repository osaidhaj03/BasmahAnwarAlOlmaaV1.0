<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $hasWebAccount = filled($data['email'] ?? null) && filled($data['password'] ?? null);

        $data['email'] = $data['email'] ?: 'user-' . Str::uuid() . '@no-login.local';
        $data['password'] = $data['password'] ?: Str::random(32);
        $data['is_active'] = $hasWebAccount ? ($data['is_active'] ?? true) : false;

        return $data;
    }
}
