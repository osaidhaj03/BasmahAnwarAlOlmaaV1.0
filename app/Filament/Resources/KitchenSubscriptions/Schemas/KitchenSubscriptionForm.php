<?php

namespace App\Filament\Resources\KitchenSubscriptions\Schemas;

use App\Models\Kitchen;
use App\Models\KitchenSubscription;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class KitchenSubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([ 
                // قسم معلومات الاشتراك
                Section::make('معلومات الاشتراك')
                    ->description('الربط بين المشترك والمطبخ')
                    ->schema([
                        // رقم الاشتراك - تلقائي ولا يمكن تعديله
                        TextInput::make('subscription_number')
                            ->label('رقم الاشتراك')
                            ->default(fn () => KitchenSubscription::generateSubscriptionNumber())
                            ->disabled() // لا يمكن تعديله
                            ->dehydrated() // لكن يتم إرسال القيمة
                            ->required()
                            ->helperText('رقم تلقائي بصيغة SUB-سنةشهر-رقم'),
                        Select::make('user_id')
                            ->label('المشترك')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->rules([
                                fn (callable $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $recordId = $get('id'); // قد يكون null في حالة الإضافة
                                    if (KitchenSubscription::hasActiveSubscription($value, $recordId) && $get('status') === 'active') {
                                        $fail('هذا المشترك لديه اشتراك فعال حالياً. لا يمكن إضافة اشتراك فعال آخر.');
                                    }
                                },
                            ]),
                        Select::make('kitchen_id')
                            ->label('المطبخ')
                            ->relationship('kitchen', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('اسم المطبخخ')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('location')
                                    ->label('الموقع')
                                    ->maxLength(255),
                                TextInput::make('phone')
                                    ->label('رقم الهاتف')
                                    ->tel()
                                    ->maxLength(20),
                                Textarea::make('description')
                                    ->label('الوصف')
                                    ->maxLength(500),
                                Toggle::make('is_active')
                                    ->label('نشط')
                                    ->default(true),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                return Kitchen::create($data)->id;
                            }),
                    ])
                    ->columns(3)
                    ->columnSpan('full'),

                // قسم مدة الاشتراك
                Section::make('مدة الاشتراك')
                    ->description('تاريخ البدء والانتهاء وحالة الاشتراك')
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('تاريخ البدء')
                            ->required()
                            ->default(now()),
                        DatePicker::make('end_date')
                            ->label('تاريخ الانتهاء')
                            ->after('start_date'),
                        Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'active' => 'فعال',
                                'paused' => 'متوقف',
                                'cancelled' => 'ملغي',
                                'expired' => 'منتهي',
                            ])
                            ->default('active')
                            ->required(),
                    ])
                    ->columns(3)
                    ->columnSpan('full'),

                // قسم المعلومات المالية
                Section::make('المعلومات المالية')
                    ->description('السعر الشهري وعدد الوجبات')
                    ->schema([
                        TextInput::make('monthly_price')
                            ->label('السعر الشهري')
                            ->required()
                            ->default(30)
                            ->numeric()
                            ->prefix('د.أ'),
                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->default(null)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpan('full'),
            ]);
    }
}
