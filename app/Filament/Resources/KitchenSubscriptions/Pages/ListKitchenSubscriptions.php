<?php

namespace App\Filament\Resources\KitchenSubscriptions\Pages;

use App\Filament\Resources\KitchenSubscriptions\KitchenSubscriptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKitchenSubscriptions extends ListRecords
{
    protected static string $resource = KitchenSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            KitchenSubscriptionResource\Widgets\KitchenSubscriptionStats::class,
        ];
    }
}
