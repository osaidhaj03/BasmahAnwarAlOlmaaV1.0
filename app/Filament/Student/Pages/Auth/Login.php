<?php

namespace App\Filament\Student\Pages\Auth;

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
                Select::make('country_code')
                    ->label('رمز الدولة')
                    ->options(User::countryCodeOptions())
                    ->searchable()
                    ->default('+962'),
                $this->getLoginFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ]);
    }

    protected function getLoginFormComponent(): TextInput
    {
        return TextInput::make('login')
            ->label('البريد الإلكتروني أو اسم المستخدم أو رقم الهاتف')
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        $login = trim((string) ($data['login'] ?? ''));
        $phone = User::normalizePhoneNumber($login);
        $countryCode = $data['country_code'] ?? '+962';

        return [
            'password' => $data['password'],
            fn ($query) => $query->where(function ($query) use ($login, $phone, $countryCode) {
                $query->where('email', $login)
                    ->orWhere('username', $login);

                if (filled($phone)) {
                    $query->orWhere(function ($query) use ($phone, $countryCode) {
                        $query->where('country_code', $countryCode)
                            ->where('phone', $phone);
                    })->orWhere('phone', $phone);
                }
            }),
        ];
    }
}
