<?php

namespace App\Filament\Resources\Subscribers\Tables;

use App\Models\MealDelivery;
use App\Models\Meal;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Filters\DateRangeFilter;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class SubscribersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-user'),
                
                TextColumn::make('phone')
                    ->label('رقم الهاتف')
                    ->searchable()
                    ->copyable()
                    ->placeholder('غير محدد')
                    ->icon('heroicon-o-phone'),
                               
                TextColumn::make('kitchenSubscriptions.subscription_number')
                    ->label('رقم الاشتراك')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->placeholder('-'),
                
                TextColumn::make('kitchenSubscriptions.status')
                    ->label('حالة الاشتراك')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'active' => 'فعال',
                        'expired' => 'منتهي',
                        'cancelled' => 'ملغي',
                        'pending' => 'قيد الانتظار',
                        default => $state ?? '-',
                    })
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'active' => 'success',
                        'expired' => 'danger',
                        'cancelled' => 'gray',
                        'pending' => 'warning',
                        default => 'gray',
                    }),

                // حالة وجبة اليوم
                TextColumn::make('today_meal_status')
                    ->label('وجبة اليوم')
                    ->getStateUsing(function ($record) {
                        $todayDelivery = MealDelivery::where('user_id', $record->id)
                            ->whereDate('delivery_date', today())
                            ->first();
                        
                        if (!$todayDelivery) return 'لم يتم التسليم';
                        return $todayDelivery->status === 'delivered' ? 'تم التسليم' : 'لم يتم التسليم';
                    })
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'تم التسليم' => 'success',
                        'لم يتم التسليم' => 'warning',
                        default => 'gray',
                    }),
                
                TextColumn::make('outstanding_balance')
                    ->label('المبالغ')
                    ->getStateUsing(function ($record) {
                        $subscription = $record->kitchenSubscriptions()->where('status', 'active')->first();
                        if (!$subscription) return 0;
                        
                        $totalInvoices = $subscription->invoices()->sum('amount');
                        $totalPaid = $subscription->invoices->sum(fn ($inv) => $inv->total_paid);
                        return $totalInvoices - $totalPaid;
                    })
                    ->money('jod')
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),
                
                IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                //SelectFilter::make('subscription_status')
                //    ->label('حالة الاشتراك')
                //    ->options([
                //        'active' => 'فعال',
                //        'expired' => 'منتهي',
                //        'cancelled' => 'ملغي',
                //        'pending' => 'قيد الانتظار',
                //    ])
                //    ->query(function ($query, array $data) {
                //        if ($data['value']) {
                //            $query->whereHas('kitchenSubscriptions', fn ($q) => $q->where('status', $data['value']));
                //        }
                //    }),
                //
                // فلتر استلام وجبة اليوم
                SelectFilter::make('today_meal')
                    ->label('وجبة اليوم')
                    ->options([
                        'delivered' => 'استلم',
                        'pending' => 'لم يستلم',
                        
                    ])
                    ->query(function ($query, array $data) {
                        if (!$data['value']) return;
                        
                        if ($data['value'] === 'delivered') {
                            $query->whereHas('mealDeliveries', fn ($q) => 
                                $q->whereDate('delivery_date', today())->where('status', 'delivered')
                            );
                        } elseif ($data['value'] === 'pending') {
                            $query->whereHas('mealDeliveries', fn ($q) => 
                                $q->whereDate('delivery_date', today())->where('status', 'pending')
                            );
                        } elseif ($data['value'] === 'missed') {
                            $query->whereHas('mealDeliveries', fn ($q) => 
                                $q->whereDate('delivery_date', today())->where('status', 'missed')
                            );
                        } elseif ($data['value'] === 'none') {
                            $query->whereDoesntHave('mealDeliveries', fn ($q) => 
                                $q->whereDate('delivery_date', today())
                            );
                        }
                    }),
                
                //TernaryFilter::make('is_active')
                //    ->label('الحالة')
                //    ->placeholder('الجميع')
                //    ->trueLabel('نشط')
                //    ->falseLabel('غير نشط'),
            ])

            ->query(fn (Builder $query) => $query->whereHas('kitchenSubscriptions', fn ($q) => $q->where('status', 'active')))

            ->recordActions([
                // زر تسليم وجبة لكل صف
                Action::make('deliverMeal')
                    ->label('تسليم وجبة')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('تأكيد تسليم الوجبة')
                    ->modalDescription(function ($record) {
                        $todayMeal = Meal::whereDate('meal_date', today())->first();
                        if (!$todayMeal) {
                            return '⚠️ لا توجد وجبة مسجلة لهذا اليوم!';
                        }
                        return "هل تريد تسليم وجبة ({$todayMeal->name}) للمشترك: {$record->name}؟";
                    })
                    ->modalSubmitActionLabel('نعم، تسليم')
                    ->action(function ($record) {
                        self::deliverMealToUser($record);
                    })
                    ->visible(function ($record) {
                        // لازم اشتراك فعال + وجبة اليوم موجودة
                        $hasActiveSubscription = $record->kitchenSubscriptions()->where('status', 'active')->exists();
                        $todayMealExists = Meal::whereDate('meal_date', today())->exists();
                        return $hasActiveSubscription && $todayMealExists;
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    // تسليم وجبات للمحددين
                    BulkAction::make('deliverMeals')
                        ->label('تسليم وجبات للمحددين')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('تأكيد التسليم الجماعي')
                        ->modalDescription(function (Collection $records) {
                            $todayMeal = Meal::whereDate('meal_date', today())->first();
                            if (!$todayMeal) {
                                return '⚠️ لا توجد وجبة مسجلة لهذا اليوم! لا يمكن التسليم.';
                            }
                            return "هل تريد تسليم وجبة ({$todayMeal->name}) لـ {$records->count()} مشترك؟";
                        })
                        ->modalSubmitActionLabel('نعم، تسليم للجميع')
                        ->action(function (Collection $records) {
                            // التحقق من وجود وجبة اليوم
                            $todayMeal = Meal::whereDate('meal_date', today())->first();
                            if (!$todayMeal) {
                                Notification::make()
                                    ->title('⚠️ لا توجد وجبة')
                                    ->body('يرجى إضافة وجبة لليوم أولاً قبل التسليم')
                                    ->danger()
                                    ->send();
                                return;
                            }
                            
                            $count = 0;
                            foreach ($records as $record) {
                                if ($record->kitchenSubscriptions()->where('status', 'active')->exists()) {
                                    self::deliverMealToUser($record);
                                    $count++;
                                }
                            }
                            
                            Notification::make()
                                ->title('✅ تم التسليم')
                                ->body("تم تسليم وجبة ({$todayMeal->name}) لـ {$count} مشترك")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            // الضغط على الصف = تسليم وجبة
            ->recordAction('deliverMeal')
            ->defaultSort('created_at', 'desc')
            ->striped();
    }

    /**
     * تسليم وجبة لمستخدم
     */
    private static function deliverMealToUser($user): void
    {
        $subscription = $user->kitchenSubscriptions()->where('status', 'active')->first();
        
        if (!$subscription) {
            Notification::make()
                ->title('خطأ')
                ->body('هذا المشترك ليس لديه اشتراك فعال')
                ->danger()
                ->send();
            return;
        }

        // التحقق من وجود تسليم سابق اليوم
        $existingDelivery = MealDelivery::where('user_id', $user->id)
            ->whereDate('delivery_date', today())
            ->first();

        if ($existingDelivery && $existingDelivery->status === 'delivered') {
            Notification::make()
                ->title('تم التسليم مسبقاً')
                ->body("الوجبة سُلّمت لـ {$user->name} في وقت سابق اليوم")
                ->warning()
                ->send();
            return;
        }

        // جلب وجبة اليوم
        $todayMeal = Meal::whereDate('meal_date', today())->first();

        if ($existingDelivery) {
            // تحديث السجل الموجود
            $existingDelivery->update([
                'status' => 'delivered',
                'delivered_at' => now(),
                'delivered_by' => Auth::id(),
            ]);
        } else {
            // إنشاء سجل جديد
            MealDelivery::create([
                'user_id' => $user->id,
                'meal_id' => $todayMeal?->id,
                'subscription_id' => $subscription->id,
                'delivery_date' => today(),
                'meal_type' => $todayMeal?->meal_type ?? 'lunch',
                'status' => 'delivered',
                'delivered_at' => now(),
                'delivered_by' => Auth::id(),
            ]);
        }

        Notification::make()
            ->title('✅ تم التسليم')
            ->body("تم تسليم الوجبة للمشترك: {$user->name}")
            ->success()
            ->send();
    }
}
