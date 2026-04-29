<?php

namespace App\Filament\Cook\Widgets;

use App\Models\MealDelivery;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class CookMealsChart extends ChartWidget
{
    protected static ?int $sort = 3;
    protected ?string $heading = 'الوجبات المسلمة (آخر 7 أيام)';
    protected ?string $maxHeight = '300px';

    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $data = collect();
        $labels = collect();

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels->push($date->format('m/d'));
            $data->push(MealDelivery::whereDate('delivery_date', $date)->delivered()->count());
        }

        return [
            'datasets' => [
                [
                    'label' => 'الوجبات المسلمة',
                    'data' => $data->toArray(),
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
