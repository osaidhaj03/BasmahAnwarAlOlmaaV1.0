<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use App\Models\Kitchen;
use App\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // القسم الأول: المعلومات الأساسية
                Section::make('المعلومات الأساسية')
                    ->description('المعلومات الشخصية الأساسية للمستخدم')
                    ->collapsible()
                    ->schema([
                        TextInput::make('name')
                            ->label('الاسم الكامل')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan('full'),

                        TextInput::make('username')
                            ->label('اسم المستخدم')
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->alphaDash()
                            ->placeholder('username'),

                        Select::make('roles')
                            ->label('الأدوار')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->live(),
                        
                        Select::make('kitchen_id')
                            ->label('المطبخ')
                            ->options(Kitchen::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('اسم المطبخ')
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
                            })
                            ->visible(fn ($get) => in_array(
                                Role::where('slug', 'cook')->first()?->id, 
                                $get('roles') ?? []
                            ))
                            ->required(fn ($get) => in_array(
                                Role::where('slug', 'cook')->first()?->id, 
                                $get('roles') ?? []
                            )),
                        
                        Select::make('gender')
                            ->label('الجنس')
                            ->options([
                                'male' => 'ذكر',
                                'female' => 'أنثى',
                            ])
                            ->nullable(),
                        
                        DatePicker::make('birth_date')
                            ->label('تاريخ الميلاد')
                            ->nullable(),

                        TextInput::make('nationality')
                            ->label('الجنسية')
                            ->maxLength(100),

                        Textarea::make('address')
                            ->label('العنوان')
                            ->maxLength(500)
                            ->columnSpan('full'),
                            
                        
                        
                        
                    ]) ->columnSpan('full') ->columns(2),
                
                // القسم الثاني: المعلومات الإضافية
                Section::make('معلومات إضافية')
                    ->description('معلومات تفصيلية حسب نوع المستخدم')
                    ->collapsible()
                    ->schema([
                        // معلومات خاصة بالطلاب
                        TextInput::make('student_id')
                            ->label(' رقم الطالب الجامعي')
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->visible(fn ($get) => in_array(
                                Role::where('slug', 'student')->first()?->id, 
                                $get('roles') ?? []
                            ))
                            ->placeholder('ادخل رقمك الجامعي إن كنت من طلاب كلية الفقه الحنفي '),
                        
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
                            ->visible(fn ($get) => !empty(array_intersect(
                                [
                                    Role::where('slug', 'student')->first()?->id,
                                    Role::where('slug', 'teacher')->first()?->id,
                                ],
                                $get('roles') ?? []
                            ))),
                        
                        
                    ]) ->columnSpan('full') ->columns(2),
                
               
                
                // القسم : معلومات الحساب
                Section::make('معلومات الحساب')
                    ->description('إعدادات معلومات الحساب مثل البريد الإلكتروني وكلمة المرور')
                    ->collapsible()
                    ->schema([
                                                
                        TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        
                        Select::make('country_code')
                            ->label('رمز الدولة')
                            ->options(\App\Models\User::countryCodeOptions())
                            ->searchable()
                            ->default('+962'),

                        TextInput::make('phone')
                            ->label(' رقم الهاتف ')
                            ->tel()
                            ->maxLength(20),
                        
                        TextInput::make('password')
                            ->label('كلمة المرور')
                            ->password()
                            ->minLength(8)
                            ->same('passwordConfirmation')
                            ->dehydrated(fn ($state): bool => filled($state)),
                        
                        TextInput::make('passwordConfirmation')
                            ->label('تأكيد كلمة المرور')
                            ->password()
                            ->dehydrated(false),
                        
                        Select::make('is_active')
                            ->label('نشط')
                            ->options([
                                true => 'نشط',
                                false => 'غير نشط',
                            ])
                           
                    ]) ->columnSpan('full') ->columns(2),
                    
                    Section::make('معلومات عامة')
                    ->description('المعلومات الشخصية العامة للمستخدم')
                    ->collapsible()
                    ->schema([
                        Textarea::make('bio')
                            ->label('نبذة شخصية')
                            ->maxLength(500)
                            ->rows(3),
                      
                    ])  ->columnSpan('full') ,
            ]);
    }
}
