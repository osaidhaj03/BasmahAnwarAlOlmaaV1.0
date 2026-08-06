<?php

namespace App\Filament\Resources\KitchenSubscriptions\Pages;

use App\Filament\Resources\KitchenSubscriptions\KitchenSubscriptionResource;
use App\Filament\Resources\KitchenSubscriptions\Widgets\KitchenSubscriptionStats;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

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
            KitchenSubscriptionStats::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('الكل')
                ->icon('heroicon-o-rectangle-stack'),
            'active' => Tab::make('الاشتراكات الفعالة')
                ->icon('heroicon-o-check-circle')
                ->query(fn ($query) => $query->where('status', 'active')),
            'paused' => Tab::make('الاشتراكات المتوقفة')
                ->icon('heroicon-o-pause-circle')
                ->query(fn ($query) => $query->where('status', 'paused')),
            'cancelled' => Tab::make('الاشتراكات الملغاة')
                ->icon('heroicon-o-x-circle')
                ->query(fn ($query) => $query->where('status', 'cancelled')),
            'expired' => Tab::make('الاشتراكات المنتهية')
                ->icon('heroicon-o-clock')
                ->query(fn ($query) => $query->where('status', 'expired')),
        ];
    }
}
