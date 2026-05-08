<?php

namespace App\Filament\Widgets;

use App\Models\KitchenExpense;
use App\Models\KitchenInvoice;
use App\Models\KitchenPayment;
use App\Models\KitchenSubscription;
use App\Models\Meal;
use App\Models\MealDelivery;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class KitchenAdminStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = Carbon::today();

        $activeSubscriptions = KitchenSubscription::where('status', 'active')->count();
        $unpaidInvoicesAmount = KitchenInvoice::whereIn('status', ['pending', 'partial', 'overdue'])->sum('amount');
        $todayPayments = KitchenPayment::whereDate('payment_date', $today)->sum('amount');
        $monthlyPayments = KitchenPayment::whereMonth('payment_date', $today->month)->whereYear('payment_date', $today->year)->sum('amount');
        $pendingMeals = MealDelivery::where('status', 'pending')->count();
        $monthlyExpenses = KitchenExpense::whereMonth('expense_date', $today->month)->whereYear('expense_date', $today->year)->sum('amount');

        return [
            Stat::make('Active subscriptions', $activeSubscriptions)
                ->description('Current meal subscriptions')
                ->descriptionIcon('heroicon-m-document-check')
                ->color('success'),

            Stat::make('Unpaid invoices', number_format($unpaidInvoicesAmount, 2))
                ->description('Pending, partial, and overdue')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),

            Stat::make('Payments today', number_format($todayPayments, 2))
                ->description($today->format('Y-m-d'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Payments this month', number_format($monthlyPayments, 2))
                ->description($today->format('Y-m'))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),

            Stat::make('Pending meals', $pendingMeals)
                ->description(Meal::count() . ' meals defined')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('warning'),

            Stat::make('Monthly expenses', number_format($monthlyExpenses, 2))
                ->description('Kitchen expenses')
                ->descriptionIcon('heroicon-m-receipt-percent')
                ->color('primary'),
        ];
    }
}
