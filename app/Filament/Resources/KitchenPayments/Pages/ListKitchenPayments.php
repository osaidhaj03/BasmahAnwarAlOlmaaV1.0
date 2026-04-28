<?php

namespace App\Filament\Resources\KitchenPayments\Pages;

use App\Filament\Resources\KitchenPayments\KitchenPaymentsResource;
use App\Filament\Widgets\AdminLatestPaymentsTable;
use App\Filament\Widgets\KitchenPaymentsPendingTransfersTable;
use App\Filament\Widgets\KitchenPaymentsSummaryWidget;
use App\Filament\Widgets\KitchenPaymentsTransferChart;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKitchenPayments extends ListRecords
{
    protected static string $resource = KitchenPaymentsResource::class;

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
            KitchenPaymentsSummaryWidget::class,
            AdminLatestPaymentsTable::class,
            KitchenPaymentsTransferChart::class,
            KitchenPaymentsPendingTransfersTable::class,
        ];
    }
}
