<?php

namespace App\Filament\Cook\Widgets;

use App\Models\KitchenPayment;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class CookPaymentsSummaryWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'ملخص سنداتي';

    private const CUSTODY_WARNING_AMOUNT = 100.00;

    protected function getStats(): array
    {
        $userId = Auth::id();

        $myPayments = KitchenPayment::query()
            ->where('collected_by', $userId);

        $pendingPayments = (clone $myPayments)->whereNull('delivered_to');
        $deliveredPayments = (clone $myPayments)->whereNotNull('delivered_to');
        $todayPayments = (clone $myPayments)->whereDate('payment_date', Carbon::today());

        $pendingAmount = (clone $pendingPayments)->sum('amount');
        $pendingCount = (clone $pendingPayments)->count();
        $deliveredCount = (clone $deliveredPayments)->count();
        $todayAmount = (clone $todayPayments)->sum('amount');

        $pendingAmountDescription = $pendingAmount >= self::CUSTODY_WARNING_AMOUNT
            ? 'تنبيه عهدة: يرجى تسليم السندات'
            : 'سندات قبض لم يتم تسليمها بعد';

        return [
            Stat::make('قيمة السندات غير المسلمة', number_format($pendingAmount, 2) . ' د.أ')
                ->description($pendingAmountDescription)
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($pendingAmount >= self::CUSTODY_WARNING_AMOUNT ? 'danger' : 'warning'),

            Stat::make('عدد السندات غير المسلمة', $pendingCount)
                ->description('بانتظار التسليم')
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger'),

            Stat::make('عدد السندات المسلمة', $deliveredCount)
                ->description('تم تسليمها لحساب داخلي')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('مجموع دفعات اليوم', number_format($todayAmount, 2) . ' د.أ')
                ->description(Carbon::today()->format('Y/m/d'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),
        ];
    }
}
