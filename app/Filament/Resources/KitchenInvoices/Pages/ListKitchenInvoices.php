<?php

namespace App\Filament\Resources\KitchenInvoices\Pages;

use App\Filament\Resources\KitchenInvoices\KitchenInvoiceResource;
use App\Filament\Widgets\KitchenInvoicesSummaryWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Livewire\Attributes\On;

class ListKitchenInvoices extends ListRecords
{
    protected static string $resource = KitchenInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modal(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            KitchenInvoicesSummaryWidget::class,
        ];
    }

    #[On('refresh')]
    public function refresh(): void
    {
        // إعادة تحميل الجدول
    }
}



