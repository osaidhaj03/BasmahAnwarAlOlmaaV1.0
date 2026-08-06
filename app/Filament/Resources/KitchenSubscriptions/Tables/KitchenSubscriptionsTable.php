<?php

namespace App\Filament\Resources\KitchenSubscriptions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
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
                EditAction::make()
                    ->modal(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('activate_subscription')
                        ->label('تفعيل الاشتراك')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn (\Illuminate\Database\Eloquent\Collection $records) => $records->each->update(['status' => 'active'])),
                    BulkAction::make('change_monthly_price')
                        ->label('تعديل قيمة الاشتراك')
                        ->icon('heroicon-o-currency-dollar')
                        ->form([
                            TextInput::make('monthly_price')
                                ->label('قيمة الاشتراك الجديدة')
                                ->numeric()
                                ->required()
                                ->minValue(0),
                        ])
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records, array $data) {
                            $records->each->update(['monthly_price' => $data['monthly_price']]);

                            Notification::make()
                                ->title('تم تعديل قيمة الاشتراك بنجاح')
                                ->body('تم تعديل قيمة ' . $records->count() . ' اشتراك/اشتراكات.')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('pause_subscription')
                        ->label('إيقاف الاشتراك')
                        ->icon('heroicon-o-pause')
                        ->color('warning')
                        ->action(fn (\Illuminate\Database\Eloquent\Collection $records) => $records->each->update(['status' => 'paused'])),
                    BulkAction::make('cancel_subscription')
                        ->label('إلغاء الاشتراك')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (\Illuminate\Database\Eloquent\Collection $records) => $records->each->update(['status' => 'cancelled'])),
                ]),
            ]);
    }
}
