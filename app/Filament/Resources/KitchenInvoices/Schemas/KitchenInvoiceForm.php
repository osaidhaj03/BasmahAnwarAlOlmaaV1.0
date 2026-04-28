<?php

namespace App\Filament\Resources\KitchenInvoices\Schemas;

use App\Models\KitchenInvoice;
use App\Models\KitchenSubscription;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class KitchenInvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // قسم معلومات الفاتورة
                Section::make('معلومات الفاتورة')
                    ->schema([
                        // اختيار المستخدم - هذا الحقل الوحيد الذي يدخله المستخدم
                        Select::make('user_id')
                            ->label('المستخدم')
                            ->options(function () {
                                // جلب المستخدمين الذين لديهم اشتراكات فعالة فقط
                                return User::whereHas('kitchenSubscriptions', function ($query) {
                                    $query->where('status', 'active');
                                })->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn (?KitchenInvoice $record) => $record !== null) // تعطيل في وضع التعديل
                            ->dehydrated() // حفظ القيمة حتى عند التعطيل
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                                if ($state) {
                                    // جلب الاشتراك الفعال للمستخدم
                                    $activeSubscription = KitchenSubscription::where('user_id', $state)
                                        ->where('status', 'active')
                                        ->first();
                                    
                                    if ($activeSubscription) {
                                        $set('subscription_id', $activeSubscription->id);
                                        // تعيين رقم الاشتراك للعرض
                                        $set('subscription_number_display', $activeSubscription->subscription_number ?? 'بدون رقم');
                                        // تعيين المبلغ من سعر الاشتراك الشهري إذا موجود، وإلا 30 دينار
                                        $set('amount', $activeSubscription->monthly_price ?? 30);
                                    } else {
                                        $set('subscription_id', null);
                                        $set('subscription_number_display', null);
                                        $set('amount', 30);
                                    }
                                }
                            }),

                        // رقم الاشتراك - للعرض فقط
                        TextInput::make('subscription_number_display')
                            ->label('رقم الاشتراك')
                            ->disabled()
                            ->dehydrated(false) // لا يتم إرساله - للعرض فقط
                            ->placeholder('سيظهر عند اختيار المستخدم'),

                        // الاشتراك - حقل مخفي لحفظ القيمة (يتم تعبئته تلقائياً)
                        \Filament\Forms\Components\Hidden::make('subscription_id'),

                        // رقم الفاتورة - تلقائي ولا يمكن تعديله
                        TextInput::make('invoice_number')
                            ->label('رقم الفاتورة')
                            ->default(fn () => KitchenInvoice::generateInvoiceNumber())
                            ->disabled() // لا يمكن تعديله
                            ->dehydrated() // لكن يتم إرسال القيمة
                            ->required(),
                    ])
                    ->columns(3)
                    ->columnSpan('full'),

                // قسم المبلغ والتواريخ
                Section::make('المبلغ والتواريخ')
                    ->schema([
                        TextInput::make('amount')
                            ->label('المبلغ المطلوب')
                            ->placeholder('يجلب المبلغ الموجود في الاشتراك')
                            ->required()
                            ->numeric()
                            ->prefix('د.أ'),

                        DatePicker::make('billing_date')
                            ->label('تاريخ الفوترة')
                            ->required()
                            ->default(now())
                            ->helperText('مثال: فاتورة شهر 2 تكون ضمن الفترة 25/1 إلى 25/2 حسب تاريخ الفوترة'),

                        // تاريخ الاستحقاق - أول الشهر القادم تلقائياً
                        DatePicker::make('due_date')
                            ->label('تاريخ الاستحقاق')
                            ->required()
                            ->default(fn () => now()->addMonth()->startOfMonth())
                            ->helperText('تلقائياً أول الشهر القادم'),
                    ])
                    ->columns(3)
                    ->columnSpan('full'),

                // قسم حالة الفاتورة
                Section::make('حالة الفاتورة')
                    ->description('الحالة تتغير تلقائياً بناءً على الدفعات')
                    ->schema([
                        Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'pending' => 'قيد الانتظار',
                                'paid' => 'مدفوعة',
                                'partial' => 'مدفوعة جزئياً',
                                'overdue' => 'متأخرة',
                                'cancelled' => 'ملغاة',
                            ])
                            ->default('pending')
                            ->disabled() // لا يمكن تعديله يدوياً
                            ->dehydrated()
                            ->helperText('تتغير تلقائياً بناءً على الدفعات المسجلة'),

                        // عرض معلومات الدفعات
                        Placeholder::make('payment_info')
                            ->label('معلومات الدفع')
                            ->content(function (?KitchenInvoice $record): string {
                                if (!$record) {
                                    return 'لم يتم حفظ الفاتورة بعد';
                                }
                                
                                $totalPaid = $record->total_paid;
                                $remaining = $record->remaining_amount;
                                
                                return "المدفوع: {$totalPaid} د.أ | المتبقي: {$remaining} د.أ";
                            })
                            ->visibleOn(['edit', 'view']),
                    ])
                    ->columns(2)
                    ->columnSpan('full'),
            ]);
    }
}

