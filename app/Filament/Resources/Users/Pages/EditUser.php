<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [
            ViewAction::make(),
            DeleteAction::make(),
        ];

        // إضافة زر تحديث كلمة المرور للمدراء والمستخدم الخاص به
        if (Auth::check() && (Auth::user()->type === 'admin' || Auth::user()->id === $this->record->id)) {
            $actions[] = Action::make('updatePassword')
                ->label('تحديث كلمة المرور')
                ->icon('heroicon-m-lock-closed')
                ->color('primary')
                ->url(route('filament.admin.resources.users.updatePassword', ['record' => $this->record]));
        }

        return $actions;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['country_code'] = $data['country_code'] ?? '+962';
        $data['phone'] = User::normalizePhoneNumber($data['phone'] ?? null);

        return $data;
    }
}
