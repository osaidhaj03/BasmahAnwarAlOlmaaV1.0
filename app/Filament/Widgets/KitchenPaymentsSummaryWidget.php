<?php

namespace App\Filament\Widgets;

use App\Models\KitchenPayment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class KitchenPaymentsSummaryWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'ملخص سندات القبض';

    protected function getStats(): array
    {
        $totalPaymentsCount = KitchenPayment::count();
        $totalCollectedAmount = KitchenPayment::sum('amount');
        $transferredAmount = KitchenPayment::whereNotNull('delivered_to')->sum('amount');
        $pendingTransferAmount = KitchenPayment::whereNull('delivered_to')->sum('amount');

        return [
            Stat::make('إجمالي السندات', $totalPaymentsCount)
                ->description('كل سندات القبض')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('إجمالي المبالغ', number_format($totalCollectedAmount, 2) . ' د.أ')
                ->description('المبالغ المقبوضة')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('المبالغ المحولة', number_format($transferredAmount, 2) . ' د.أ')
                ->description('تم تسليمها إلى حساب داخلي')
                ->descriptionIcon('heroicon-m-arrow-right-circle')
                ->color('info'),

            Stat::make('المبالغ بانتظار التسليم', number_format($pendingTransferAmount, 2) . ' د.أ')
                ->description('لم تُحوّل بعد')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}