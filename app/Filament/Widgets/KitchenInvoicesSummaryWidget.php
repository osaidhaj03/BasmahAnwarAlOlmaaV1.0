<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\KitchenInvoices\Pages\ListKitchenInvoices;
use App\Models\KitchenInvoice;
use App\Support\KitchenBillingPeriod;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class KitchenInvoicesSummaryWidget extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?int $sort = 2;

    protected ?string $heading = 'ملخص فواتير المطبخ';

    protected function getTablePage(): string
    {
        return ListKitchenInvoices::class;
    }

    protected function getStats(): array
    {
        $periodInvoices = $this->tableFilters === null
            ? $this->getCurrentPeriodQuery()
            : $this->getPageTableQuery();

        $periodLabel = KitchenBillingPeriod::labelFromFilterData(data_get($this->tableFilters, 'billing_period'));

        $totalInvoices = (clone $periodInvoices)->count();
        $paidInvoices = (clone $periodInvoices)->where('status', 'paid')->count();
        $unpaidInvoices = (clone $periodInvoices)->whereIn('status', ['pending', 'partial'])->count();
        $overdueInvoices = (clone $periodInvoices)->where('status', 'overdue')->count();

        return [
            Stat::make('فواتير الفترة', $totalInvoices)
                ->description($periodLabel)
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make('الفواتير المدفوعة', $paidInvoices)
                ->description('ضمن نفس الفلتر')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('الفواتير غير المسددة', $unpaidInvoices)
                ->description('قيد الانتظار أو مدفوعة جزئيا')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('الفواتير المتأخرة', $overdueInvoices)
                ->description('تحتاج متابعة')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }

    protected function getCurrentPeriodQuery()
    {
        [$start, $end] = KitchenBillingPeriod::boundsFromMonth(KitchenBillingPeriod::currentMonth());

        return KitchenInvoice::query()
            ->whereDate('billing_date', '>=', $start->toDateString())
            ->whereDate('billing_date', '<', $end->toDateString());
    }
}
