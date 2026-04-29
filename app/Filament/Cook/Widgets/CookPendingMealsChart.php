<?php

namespace App\Filament\Cook\Widgets;

use App\Models\MealDelivery;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class CookPendingMealsChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'الوجبات غير المسلمة (آخر 7 أيام)';

    protected ?string $maxHeight = '300px';

    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $data = collect();
        $labels = collect();

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);

            $labels->push($date->format('m/d'));
            $data->push(
                MealDelivery::query()
                    ->whereDate('delivery_date', $date)
                    ->pending()
                    ->count()
            );
        }

        return [
            'datasets' => [
                [
                    'label' => 'الوجبات غير المسلمة',
                    'data' => $data->toArray(),
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.2)',
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
