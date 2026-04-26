<?php

namespace App\Filament\Resources\KitchenSubscriptions\Widgets;

use App\Models\KitchenSubscription;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class KitchenSubscriptionStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('الاشتراكات الفعالة', KitchenSubscription::where('status', 'active')->count())
                ->description('إجمالي عدد الطلاب النشطين')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),
            Stat::make('الاشتراكات المتوقفة', KitchenSubscription::where('status', 'paused')->count())
                ->description('اشتراكات موقوفة مؤقتاً')
                ->descriptionIcon('heroicon-m-pause-circle')
                ->color('warning'),
            Stat::make('الاشتراكات الملغاة', KitchenSubscription::where('status', 'cancelled')->count())
                ->description('اشتراكات تم إلغاؤها')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
            Stat::make('الاشتراكات المنتهية', KitchenSubscription::where('status', 'expired')->count())
                ->description('اشتراكات انتهت مدتها')
                ->descriptionIcon('heroicon-m-clock')
                ->color('gray'),
        ];
    }
}
