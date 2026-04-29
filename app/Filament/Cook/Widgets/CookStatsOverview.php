<?php

namespace App\Filament\Cook\Widgets;

use App\Models\KitchenInvoice;
use App\Models\KitchenSubscription;
use App\Models\MealDelivery;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class CookStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    
    
    protected function getStats(): array
    {
        $today = Carbon::today()->format('Y/m/d');
        $todayMealsDelivered = MealDelivery::today()->delivered()->count();
        $todayMeals = MealDelivery::today()->count();

        return [
            Stat::make("وجبات اليوم ({$today})", $todayMeals)
                ->description('إجمالي وجبات اليوم')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),
            Stat::make('الوجبات المسلمة اليوم', $todayMealsDelivered)
                ->description('عدد الوجبات التي تم تسليمها اليوم')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('إجمالي الاشتراكات الفعالة', KitchenSubscription::active()->count())
                ->description('الاشتراكات النشطة حالياً')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('info'),
            //Stat::make('إجمالي المشتركين', KitchenSubscription::distinct('user_id')->count('user_id'))
            //    ->description('عدد الزبائن المشتركين')
            //    ->descriptionIcon('heroicon-m-users')
            //    ->color('info'),
            //Stat::make('المبالغ غير المحصلة', KitchenInvoice::unpaid()->sum('amount') . ' د.أ')
            //    ->description('الفواتير غير المدفوعة')
            //    ->descriptionIcon('heroicon-m-exclamation-triangle')
            //    ->color('danger'),
            //Stat::make('رصيد الطباخ', KitchenInvoice::paid()->sum('amount') . ' د.أ')
            //    ->descriptionIcon('heroicon-m-banknotes')
            //    ->color('success'),
        ];
    }
}
