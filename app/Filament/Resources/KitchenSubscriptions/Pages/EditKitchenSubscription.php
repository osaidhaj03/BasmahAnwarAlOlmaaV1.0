<?php

namespace App\Filament\Resources\KitchenSubscriptions\Pages;

use App\Filament\Resources\KitchenSubscriptions\KitchenSubscriptionResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditKitchenSubscription extends EditRecord
{
    protected static string $resource = KitchenSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function ($record, DeleteAction $action) {
                    if ($record->deliveries()->exists()) {
                        Notification::make()
                            ->title('لا يمكن حذف الاشتراك')
                            ->body('هذا الاشتراك مرتبط بسجلات تسليم وجبات، لذلك لا يمكن حذفه.')
                            ->danger()
                            ->persistent()
                            ->send();

                        $action->cancel();
                    }
                }),
        ];
    }
}
