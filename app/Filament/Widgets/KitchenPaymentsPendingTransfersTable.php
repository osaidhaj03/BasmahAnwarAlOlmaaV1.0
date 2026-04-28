<?php

namespace App\Filament\Widgets;

use App\Models\KitchenPayment;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class KitchenPaymentsPendingTransfersTable extends BaseWidget
{
    protected static ?int $sort = 4;

    protected static ?string $heading = 'السندات بانتظار التسليم';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                KitchenPayment::query()->with(['subscription.user', 'collector'])->whereNull('delivered_to')->latest('payment_date')->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('subscription.user.name')
                    ->label('اسم المشترك'),
                Tables\Columns\TextColumn::make('collector.name')
                    ->label('المحصّل'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('JOD'),
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('تاريخ الدفع')
                    ->date(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('طريقة الدفع')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'cash' => 'نقداً',
                        'bank_transfer' => 'تحويل بنكي',
                        default => $state ?? '-',
                    }),
            ])
            ->paginated(false);
    }
}