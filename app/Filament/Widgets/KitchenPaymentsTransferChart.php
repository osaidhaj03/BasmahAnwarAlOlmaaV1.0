<?php

namespace App\Filament\Widgets;

use App\Models\KitchenPayment;
use Filament\Widgets\ChartWidget;

class KitchenPaymentsTransferChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'التحويلات حسب المستلم';

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $rows = KitchenPayment::query()
            ->with('deliveredTo')
            ->selectRaw('delivered_to, SUM(amount) as total_amount')
            ->whereNotNull('delivered_to')
            ->groupBy('delivered_to')
            ->orderByDesc('total_amount')
            ->limit(6)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'المبالغ المحولة',
                    'data' => $rows->map(fn ($row) => (float) $row->total_amount)->toArray(),
                    'backgroundColor' => '#3b82f6',
                    'borderColor' => '#3b82f6',
                ],
            ],
            'labels' => $rows->map(fn ($row) => $row->deliveredTo?->name ?? 'غير محدد')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}