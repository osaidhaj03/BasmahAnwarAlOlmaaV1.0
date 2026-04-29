<?php

namespace App\Filament\Cook\Widgets;

use App\Models\KitchenInvoice;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class SubscribersWithUnpaidInvoicesTable extends BaseWidget
{
    protected static ?int $sort = 4;
    protected static ?string $heading = 'المشتركون - فواتير غير مدفوعة';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                KitchenInvoice::query()->unpaid()->with(['user', 'subscription'])->latest('due_date')
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('اسم المشترك')
                    ->searchable(),
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('رقم الفاتورة'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('JOD'),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('تاريخ الاستحقاق')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'overdue' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'قيد الانتظار',
                        'overdue' => 'متأخرة',
                        default => $state,
                    }),
            ])
            ->defaultSort('due_date', 'asc')
            ->paginated([5, 10, 25]);
    }
}
