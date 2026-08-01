<?php

namespace App\Filament\Resources\KitchenInvoices\Pages;

use App\Filament\Resources\KitchenInvoices\KitchenInvoiceResource;
use App\Filament\Widgets\KitchenInvoicesSummaryWidget;
use Filament\Actions\CreateAction;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Resources\Pages\ListRecords;
use Livewire\Attributes\On;

class ListKitchenInvoices extends ListRecords
{
    use ExposesTableToWidgets;

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

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('الكل')
                ->icon('heroicon-o-document-text'),
            'pending' => Tab::make('قيد الانتظار')
                ->icon('heroicon-o-clock')
                ->query(fn ($query) => $query->where('status', 'pending')),
            'partial' => Tab::make('مدفوعة جزئيا')
                ->icon('heroicon-o-adjustments-horizontal')
                ->query(fn ($query) => $query->where('status', 'partial')),
            'paid' => Tab::make('مدفوعة')
                ->icon('heroicon-o-check-circle')
                ->query(fn ($query) => $query->where('status', 'paid')),
            'overdue' => Tab::make('متأخرة')
                ->icon('heroicon-o-exclamation-triangle')
                ->query(fn ($query) => $query->where('status', 'overdue')),
            'cancelled' => Tab::make('ملغاة')
                ->icon('heroicon-o-x-circle')
                ->query(fn ($query) => $query->where('status', 'cancelled')),
        ];
    }

    #[On('refresh')]
    public function refresh(): void
    {
        // إعادة تحميل الجدول
    }
}

