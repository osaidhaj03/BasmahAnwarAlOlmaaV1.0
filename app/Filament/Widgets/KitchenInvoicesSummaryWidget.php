<?php

namespace App\Filament\Widgets;

use App\Models\KitchenInvoice;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class KitchenInvoicesSummaryWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'ملخص فواتير المطبخ';

    protected function getStats(): array
    {
        $today = Carbon::today();
        $periodStart = $today->copy()->day(25)->startOfDay();

        if ($today->day < 25) {
            $periodStart->subMonthNoOverflow();
        }

        $periodEnd = $periodStart->copy()->addMonthNoOverflow()->startOfDay();

        $periodInvoices = KitchenInvoice::query()
            ->whereDate('billing_date', '>=', $periodStart->toDateString())
            ->whereDate('billing_date', '<', $periodEnd->toDateString());

        $totalInvoices = (clone $periodInvoices)->count();
        $paidInvoices = (clone $periodInvoices)->where('status', 'paid')->count();
        $unpaidInvoices = (clone $periodInvoices)->whereIn('status', ['pending', 'partial'])->count();
        $overdueInvoices = (clone $periodInvoices)->where('status', 'overdue')->count();

        return [
            Stat::make('فواتير الدورة الحالية', $totalInvoices)
                ->description($periodStart->format('d/m') . ' - ' . $periodEnd->format('d/m'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make('الفواتير المدفوعة', $paidInvoices)
                ->description('ضمن نفس الدورة')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('الفواتير غير المسددة', $unpaidInvoices)
                ->description('قيد الانتظار أو مدفوعة جزئياً')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('الفواتير المتأخرة', $overdueInvoices)
                ->description('تحتاج متابعة')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }
}
