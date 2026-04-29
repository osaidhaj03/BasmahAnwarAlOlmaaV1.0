<?php

namespace App\Filament\Cook\Widgets;

use App\Models\KitchenPayment;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class CookPaymentsChart extends ChartWidget
{
    protected static ?int $sort = 3;
    protected ?string $heading = 'المبالغ المحصلة (آخر 7 أيام)';
    protected ?string $maxHeight = '300px';

    public static function canView(): bool
    {
        return false;
    }

    protected function getData(): array
    {
        $data = collect();
        $labels = collect();

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels->push($date->format('m/d'));
            $data->push(KitchenPayment::whereDate('payment_date', $date)->sum('amount'));
        }

        return [
            'datasets' => [
                [
                    'label' => 'المبالغ المحصلة (د.أ)',
                    'data' => $data->toArray(),
                    'backgroundColor' => '#3b82f6',
                    'borderColor' => '#3b82f6',
                ],
            ],
            'labels' => $labels->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
