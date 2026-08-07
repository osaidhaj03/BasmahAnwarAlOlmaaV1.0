<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class Login extends BaseLogin
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('login_method')
                    ->label('طريقة تسجيل الدخول')
                    ->options([
                        'email' => 'البريد الإلكتروني',
                        'username' => 'اسم المستخدم',
                        'phone' => 'رقم الهاتف',
                    ])
                    ->default('email')
                    ->live()
                    ->required(),

                TextInput::make('email')
                    ->label('البريد الإلكتروني')
                    ->email()
                    ->required(fn ($get): bool => $get('login_method') === 'email')
                    ->visible(fn ($get): bool => $get('login_method') === 'email')
                    ->autocomplete('email')
                    ->autofocus()
                    ->extraInputAttributes(['tabindex' => 1]),

                TextInput::make('username')
                    ->label('اسم المستخدم')
                    ->required(fn ($get): bool => $get('login_method') === 'username')
                    ->visible(fn ($get): bool => $get('login_method') === 'username')
                    ->autocomplete('username')
                    ->autofocus()
                    ->extraInputAttributes(['tabindex' => 1]),

                Select::make('country_code')
                    ->label('رمز الدولة')
                    ->options(User::countryCodeOptions())
                    ->searchable()
                    ->default('+962')
                    ->required(fn ($get): bool => $get('login_method') === 'phone')
                    ->visible(fn ($get): bool => $get('login_method') === 'phone'),

                TextInput::make('phone')
                    ->label('رقم الهاتف')
                    ->tel()
                    ->required(fn ($get): bool => $get('login_method') === 'phone')
                    ->visible(fn ($get): bool => $get('login_method') === 'phone')
                    ->autocomplete('tel')
                    ->autofocus()
                    ->extraInputAttributes(['tabindex' => 1]),

                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ]);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'password' => $data['password'],
            fn ($query) => match ($data['login_method'] ?? 'email') {
                'username' => $query->where('username', trim((string) ($data['username'] ?? ''))),
                'phone' => $query->where(function ($query) use ($data) {
                    $phone = User::normalizePhoneNumber($data['phone'] ?? null);
                    $countryCode = $data['country_code'] ?? '+962';

                    $query->where(function ($query) use ($phone, $countryCode) {
                        $query->where('country_code', $countryCode)
                            ->where('phone', $phone);
                    })->orWhere('phone', $phone);
                }),
                default => $query->where('email', trim((string) ($data['email'] ?? ''))),
            },
        ];
    }
}
