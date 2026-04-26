<?php

namespace App\Filament\Resources\Lessons\Schemas;

use App\Models\LessonSection;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class LessonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('معلومات الدورة')
                    ->description('المعلومات الأساسية للدورة')
                    ->collapsible()
                    ->schema([
                        TextInput::make('title')
                            ->label('عنوان الدورة')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('وصف الدورة')
                            ->rows(4)
                            ->columnSpanFull(),

                        Select::make('teacher_id')
                            ->label('المعلم')
                            ->options(User::query()->where('type', 'teacher')->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('lesson_section_id')
                            ->label('القسم')
                            ->options(LessonSection::query()->active()->ordered()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'active' => 'نشط',
                                'cancelled' => 'ملغي',
                                'completed' => 'مكتمل',
                            ])
                            ->default('active')
                            ->required(),

                        TextInput::make('max_students')
                            ->label('الحد الأقصى للطلاب')
                            ->numeric()
                            ->minValue(1)
                            ->nullable()
                            ->placeholder('غير محدود'),

                        Toggle::make('is_mandatory')
                            ->label('إجبارية (يُحسب الحضور والغياب)')
                            ->default(true),
                    ])
                    ->columns(2)
                    ->columnSpan('full'),

                Section::make('الجدول الزمني')
                    ->description('تاريخ ووقت وأيام الدورة')
                    ->collapsible()
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('تاريخ البداية')
                            ->required()
                            ->default(now()),

                        DatePicker::make('end_date')
                            ->label('تاريخ النهاية')
                            ->afterOrEqual('start_date')
                            ->nullable(),

                        TextInput::make('start_time')
                            ->label('وقت البداية')
                            ->type('time')
                            ->required(),

                        TextInput::make('end_time')
                            ->label('وقت النهاية')
                            ->type('time')
                            ->required(),

                        \Filament\Forms\Components\CheckboxList::make('lesson_days')
                            ->label('أيام الدورة')
                            ->options([
                                'sunday' => 'الأحد',
                                'monday' => 'الإثنين',
                                'tuesday' => 'الثلاثاء',
                                'wednesday' => 'الأربعاء',
                                'thursday' => 'الخميس',
                                'friday' => 'الجمعة',
                                'saturday' => 'السبت',
                            ])
                            ->columns(4)
                            ->columnSpanFull(),

                        Toggle::make('is_recurring')
                            ->label('متكرر')
                            ->default(true),
                    ])
                    ->columns(2)
                    ->columnSpan('full'),

                Section::make('المكان')
                    ->description('طريقة وموقع/رابط اللقاء')
                    ->collapsible()
                    ->schema([
                        Select::make('location_type')
                            ->label('نوع المكان')
                            ->options([
                                'online' => 'أونلاين',
                                'offline' => 'حضوري',
                            ])
                            ->default('offline')
                            ->required()
                            ->live(),

                        TextInput::make('location_details')
                            ->label('تفاصيل المكان')
                            ->maxLength(255)
                            ->nullable()
                            ->visible(fn (Get $get) => $get('location_type') === 'offline'),

                        TextInput::make('meeting_link')
                            ->label('رابط اللقاء')
                            ->url()
                            ->maxLength(255)
                            ->nullable()
                            ->visible(fn (Get $get) => $get('location_type') === 'online')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpan('full'),

                Section::make('ملاحظات')
                    ->collapsible()
                    ->schema([
                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columnSpan('full'),
            ]);
    }
}
