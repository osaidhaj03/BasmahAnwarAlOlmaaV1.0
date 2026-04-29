<?php

namespace App\Filament\Resources\KitchenPayments\Tables;

use App\Models\KitchenPayment;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class KitchenPaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(fn () => KitchenPayment::query()->with(['subscription.user', 'invoice', 'collector', 'deliveredTo']))
            ->columns([
                TextColumn::make('subscription.user.name')
                    ->label('المشترك')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('invoice.invoice_number')
                    ->label('رقم الفاتورة')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('JOD')
                    ->sortable(),
                TextColumn::make('payment_date')
                    ->label('تاريخ الدفع')
                    ->date()
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label('طريقة الدفع')
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'cash' => 'نقداً',
                        'bank_transfer' => 'تحويل بنكي',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'cash' => 'success',
                        'bank_transfer' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('collector.name')
                    ->label('المحصّل')
                    ->searchable(),
                TextColumn::make('deliveredTo.name')
                    ->label('تم التسليم إلى')
                    ->placeholder('لم يتم التسليم')
                    ->badge()
                    ->color('primary')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('delivery_status')
                    ->label('حالة التسليم')
                    ->options([
                        'pending' => 'غير مسلّم',
                        'delivered' => 'مسلّم',
                    ])
                    ->query(function ($query, array $data) {
                        return match ($data['value'] ?? null) {
                            'pending' => $query->whereNull('delivered_to'),
                            'delivered' => $query->whereNotNull('delivered_to'),
                            default => $query,
                        };
                    }),

                Filter::make('payment_date')
                    ->form([
                        DatePicker::make('from')
                            ->label('من تاريخ')
                            ->placeholder('اختر بداية الفترة')
                            ->native(false)
                            ->displayFormat('Y-m-d'),
                        DatePicker::make('to')
                            ->label('إلى تاريخ')
                            ->placeholder('اختر نهاية الفترة')
                            ->native(false)
                            ->displayFormat('Y-m-d'),
                    ])
                    ->columns(2)
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['from'] && !$data['to']) {
                            return null;
                        }

                        $from = $data['from'] ? Carbon::parse($data['from'])->format('Y-m-d') : 'البداية';
                        $to = $data['to'] ? Carbon::parse($data['to'])->format('Y-m-d') : 'الآن';

                        return "تاريخ الدفع: {$from} - {$to}";
                    })
                    ->query(function ($query, array $data) {
                        if (!empty($data['from'])) {
                            $query->whereDate('payment_date', '>=', $data['from']);
                        }

                        if (!empty($data['to'])) {
                            $query->whereDate('payment_date', '<=', $data['to']);
                        }
                    }),

                SelectFilter::make('collected_by')
                    ->label('المحصّل')
                    ->options(fn () => User::query()
                        ->active()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->toArray())
                    ->searchable()
                    ->preload(),

                SelectFilter::make('delivered_to')
                    ->label('تم التسليم إلى')
                    ->options(fn () => User::query()
                        ->active()
                        ->whereHas('roles', fn ($query) => $query->where('slug', 'admin'))
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->toArray())
                    ->searchable()
                    ->preload(),

                SelectFilter::make('payment_method')
                    ->label('طريقة الدفع')
                    ->options([
                        'cash' => 'نقداً',
                        'bank_transfer' => 'تحويل بنكي',
                        'credit_balance' => 'خصم من الرصيد المتاح',
                    ]),
            ])
            ->recordActions([
                // التعديل مغلق - الدفعات لا يمكن تعديلها
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('transfer_selected')
                        ->label('تم التسليم إلى')
                        ->icon('heroicon-o-arrow-right-circle')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->modalHeading('تسليم السندات إلى حساب داخلي')
                        ->modalDescription('سيتم تعيين السندات المحددة إلى الحساب المختار. المحصّل الأصلي سيبقى كما هو.')
                        ->form([
                            Select::make('delivered_to')
                                ->label('تم التسليم إلى')
                                ->options(fn () => self::transferRecipientOptions())
                                ->searchable()
                                ->preload()
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $recipient = User::find($data['delivered_to'] ?? null);

                            if (!$recipient) {
                                Notification::make()
                                    ->title('تعذر تنفيذ التحويل')
                                    ->body('لم يتم العثور على الحساب المستلم.')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            if (!$recipient->hasRole('admin')) {
                                Notification::make()
                                    ->title('تعذر تنفيذ التحويل')
                                    ->body('المستلم يجب أن يكون حساب admin.')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            $updatedCount = 0;

                            foreach ($records as $record) {
                                $record->update([
                                    'delivered_to' => $recipient->id,
                                ]);

                                $updatedCount++;
                            }

                            Notification::make()
                                ->title('تم التسليم إلى')
                                ->body('تم تحويل ' . $updatedCount . ' سند/سندات إلى ' . $recipient->name)
                                ->success()
                                ->send();
                        })
                        ->visible(fn () => !empty(self::transferRecipientOptions()))
                        ->deselectRecordsAfterCompletion(),
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
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('payment_date', 'desc');
    }

    private static function transferRecipientOptions(): array
    {
        $currentUser = Auth::user();

        if (!$currentUser || !$currentUser->hasRole(['admin', 'cook'])) {
            return [];
        }

        return User::query()
            ->active()
            ->whereHas('roles', fn ($query) => $query->where('slug', 'admin'))
            ->when($currentUser->hasRole('admin'), fn ($query) => $query->whereKeyNot($currentUser->id))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }
}
