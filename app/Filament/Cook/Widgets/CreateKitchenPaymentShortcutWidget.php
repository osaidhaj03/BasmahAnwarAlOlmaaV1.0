<?php

namespace App\Filament\Cook\Widgets;

use App\Filament\Resources\KitchenPayments\KitchenPaymentsResource;
use Filament\Widgets\Widget;

class CreateKitchenPaymentShortcutWidget extends Widget
{
    protected static ?int $sort = 0;

    protected string $view = 'filament.cook.widgets.create-kitchen-payment-shortcut-widget';

    protected int | string | array $columnSpan = 'full';

    public function getCreatePaymentUrl(): string
    {
        return KitchenPaymentsResource::getUrl('create');
    }
}
