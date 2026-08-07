<?php

namespace App\Filament\Resources\Students\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // القسم الأول: المعلومات الأساسية
                Section::make('المعلومات الأساسية')
                    ->description('المعلومات الشخصية الأساسية للطالب')
                    ->collapsible()
                    ->schema([
                        TextInput::make('name')
                            ->label('الاسم الكامل')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('أدخل الاسم الكامل للطالب')
                            ->columnSpan('full'),
                        
                        Select::make('gender')
                            ->label('الجنس')
                            ->options([
                                'male' => 'ذكر',
                                'female' => 'أنثى',
                            ])
                            ->nullable()
                            ->placeholder('اختر الجنس'),
                        
                        DatePicker::make('birth_date')
                            ->label('تاريخ الميلاد')
                            ->maxDate(now()->subYears(5))
                            ->displayFormat('Y-m-d')
                            ->nullable(),

                        TextInput::make('nationality')
                            ->label('الجنسية')
                            ->maxLength(100)
                            ->placeholder('أدخل الجنسية'),

                        Textarea::make('address')
                            ->label('العنوان')
                            ->maxLength(500)
                            ->rows(3)
                            ->placeholder('أدخل العنوان الكامل')
                            ->columnSpan('full'),
                    ])->columnSpan('full')
                    ->columns(2),
                
                // القسم الثاني: المعلومات الأكاديمية
                Section::make('المعلومات الأكاديمية')
                    ->description('المعلومات الدراسية والأكاديمية')
                    ->collapsible()
                    ->schema([
                        TextInput::make('student_id')
                            ->label('الرقم الجامعي')
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->placeholder('الرقم الجامعي لطلبة كلية الفقه الحنفي')
                            ->helperText('الرقم الجامعي لطلبة كلية الفقه الحنفي'),
                        
                        Select::make('academic_level')
                            ->label('المستوى الأكاديمي')
                            ->options([
                                'bachelor' => 'بكالوريس',
                                'master' => 'ماجستير',
                                'doctorate' => 'دكتوراه',
                                'intermediate_diploma' => 'دبلوم متوسط',
                                'higher_diploma' => 'دبلوم عالي',
                                'other' => 'أخرى',
                            ])
                            ->nullable()
                            ->placeholder('اختر المستوى الأكاديمي'),
                    ])->columnSpan('full') 
                   ,
                
                // القسم الثالث: معلومات الحساب
                Section::make('معلومات الحساب')
                    ->description('إعدادات الحساب وكلمة المرور')
                    ->collapsible()
                    ->schema([
                        TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('student@example.com'),
                        
                        TextInput::make('username')
                            ->label('اسم المستخدم')
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->alphaDash()
                            ->placeholder('username'),

                        Select::make('country_code')
                            ->label('رمز الدولة')
                            ->options(\App\Models\User::countryCodeOptions())
                            ->searchable()
                            ->default('+962'),

                        TextInput::make('phone')
                            ->label('رقم الهاتف')
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('+966 50 123 4567'),

                        TextInput::make('password')
                            ->label('كلمة المرور')
                            ->password()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->minLength(8)
                            ->placeholder('أدخل كلمة مرور قوية')
                            ->helperText('يجب أن تكون كلمة المرور 8 أحرف على الأقل')
                            ->same('passwordConfirmation')
                            ->dehydrated(fn ($state): bool => filled($state)),

                        TextInput::make('passwordConfirmation')
                            ->label('تأكيد كلمة المرور')
                            ->password()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->placeholder('أعد إدخال كلمة المرور')
                            ->dehydrated(false),
                        
                        Select::make('is_active')
                            ->label('حالة الحساب')
                            ->options([
                                true => 'نشط',
                                false => 'غير نشط',
                            ])
                            ->default(true)
                            ->required(),
                    ])->columnSpan('full')
                    ->columns(2),
                
                // القسم الرابع: معلومات عامة
                Section::make('معلومات عامة')
                    ->description('النبذة الشخصية والمعلومات الإضافية')
                    ->collapsible()
                    ->schema([
                        Textarea::make('bio')
                            ->label('نبذة شخصية')
                            ->maxLength(500)
                            ->rows(4)
                            ->placeholder('أدخل نبذة شخصية عن الطالب')
                            ->columnSpan('full'),
                    ])->columnSpan('full'),
            ]);
    }
}
