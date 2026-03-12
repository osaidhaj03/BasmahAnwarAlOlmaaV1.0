<?php

namespace App\Filament\Resources\KitchenInvoices\Tables;

use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use AlperenErsoy\FilamentExport\Actions\FilamentExportHeaderAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

class KitchenInvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('رقم الفاتورة')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('المشترك')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subscription.kitchen.name')
                    ->label('المطبخ')
                    ->searchable(),
                TextColumn::make('amount')
                    ->label('المبلغ المطلوب')
                    ->money('JOD')
                    ->sortable(),
                TextColumn::make('total_paid')
                    ->label('المدفوع')
                    ->money('JOD')
                    ->color('success'),
                TextColumn::make('remaining_amount')
                    ->label('المتبقي')
                    ->money('JOD')
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),
                TextColumn::make('billing_date')
                    ->label('تاريخ الفوترة')
                    ->date()
                    ->sortable(),
                TextColumn::make('due_date')
                    ->label('تاريخ الاستحقاق')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'pending' => 'قيد الانتظار',
                        'paid' => 'مدفوعة',
                        'partial' => 'مدفوعة جزئياً',
                        'overdue' => 'متأخرة',
                        'cancelled' => 'ملغاة',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'partial' => 'info',
                        'overdue' => 'danger',
                        'cancelled' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'قيد الانتظار',
                        'paid' => 'مدفوعة',
                        'partial' => 'مدفوعة جزئياً',
                        'overdue' => 'متأخرة',
                        'cancelled' => 'ملغاة',
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->modal(),
                DeleteAction::make()
                    ->before(function ($record, DeleteAction $action) {
                        if ($record->allocations()->count() > 0) {
                            \Filament\Notifications\Notification::make()
                                ->title('⚠️ لا يمكن حذف الفاتورة')
                                ->body('هذه الفاتورة مرتبطة بدفعات. يرجى حذف الدفعات أولاً قبل حذف الفاتورة.')
                                ->danger()
                                ->persistent()
                                ->send();
                            
                            $action->cancel();
                        }
                    }),
            ])
            ->headerActions([
                FilamentExportHeaderAction::make('export')
                    ->label('تصدير')
                    ->fileName('Kitchen_Invoices')
                    ->defaultFormat('xlsx')
                    ->defaultPageOrientation('landscape'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('calculate_total')
                        ->label('حساب المجموع')
                        ->icon('heroicon-o-calculator')
                        ->action(function (Collection $records) {
                            $total = $records->sum('amount');
                            Notification::make()
                                ->title('المجموع: ' . number_format($total, 2) . ' JOD')
                                ->success()
                                ->persistent()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make()
                        ->before(function ($records, DeleteBulkAction $action) {
                            $hasPayments = $records->filter(fn ($record) => $record->allocations()->count() > 0);
                            
                            if ($hasPayments->isNotEmpty()) {
                                \Filament\Notifications\Notification::make()
                                    ->title('⚠️ لا يمكن حذف بعض الفواتير')
                                    ->body('الفواتير التالية مرتبطة بدفعات ولا يمكن حذفها: ' . $hasPayments->pluck('invoice_number')->join(', '))
                                    ->danger()
                                    ->persistent()
                                    ->send();
                                
                                $action->cancel();
                            }
                        }),
                    FilamentExportBulkAction::make('export')
                        ->label('تصدير المحدد')
                        ->fileName('Selected_Invoices')
                        ->defaultFormat('xlsx')
                        ->defaultPageOrientation('landscape'),
                ]),
            ])
            ->defaultSort('billing_date', 'desc');
    }
}

