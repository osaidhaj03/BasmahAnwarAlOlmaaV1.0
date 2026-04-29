<?php

namespace App\Filament\Cook\Widgets;

use App\Models\KitchenPayment;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestPaymentsTable extends BaseWidget
{
    protected static ?int $sort = 5;
    protected static ?string $heading = 'آخر الدفعات';

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                KitchenPayment::query()->with(['subscription.user'])->latest('payment_date')->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('subscription.user.name')
                    ->label('اسم المشترك'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('SAR'),
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('تاريخ الدفع')
                    ->date()
                    ->sortable(),
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
