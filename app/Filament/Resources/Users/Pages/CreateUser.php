<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $hasWebAccount = filled($data['password'] ?? null)
            && (filled($data['email'] ?? null) || filled($data['username'] ?? null) || filled($data['phone'] ?? null));

        $data['email'] = $data['email'] ?: 'user-' . Str::uuid() . '@no-login.local';
        $data['password'] = $data['password'] ?: Str::random(32);
        $data['country_code'] = $data['country_code'] ?? '+962';
        $data['phone'] = User::normalizePhoneNumber($data['phone'] ?? null);
        $data['is_active'] = $hasWebAccount ? ($data['is_active'] ?? true) : false;

        return $data;
    }
}
