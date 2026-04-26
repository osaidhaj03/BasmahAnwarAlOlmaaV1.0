<?php

namespace App\Filament\Resources\KitchenSubscriptions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class KitchenSubscriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subscription_number')
                    ->label('رقم الاشتراك')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('المشترك')  
                    ->searchable(),
                TextColumn::make('kitchen.name')
                    ->label('المطبخ')
                    ->searchable(),
                TextColumn::make('start_date')
                    ->label('تاريخ البدء')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('تاريخ الانتهاء')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'فعال',
                        'paused' => 'متوقف',
                        'cancelled' => 'ملغي',
                        'expired' => 'منتهي',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'paused' => 'warning',
                        'cancelled' => 'danger',
                        'expired' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('monthly_price')
                    ->label('قيمة الاشتراك الشهري')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    \Filament\Tables\Actions\BulkAction::make('pause_subscription')
                        ->label('إيقاف الاشتراك')
                        ->icon('heroicon-o-pause')
                        ->color('warning')
                        ->action(fn (\Illuminate\Database\Eloquent\Collection $records) => $records->each->update(['status' => 'paused'])),
                    \Filament\Tables\Actions\BulkAction::make('cancel_subscription')
                        ->label('إلغاء الاشتراك')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (\Illuminate\Database\Eloquent\Collection $records) => $records->each->update(['status' => 'cancelled'])),
                ]),
            ]);
    }
}
