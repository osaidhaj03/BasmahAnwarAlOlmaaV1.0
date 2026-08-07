<?php

namespace App\Filament\Student\Pages\Auth;

use App\Models\User;
use App\Filament\Student\Pages\StudentDashboard;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Auth\Pages\Register as BaseRegister;
use Illuminate\Support\Carbon;

class Register extends BaseRegister
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('المعلومات الأساسية')
                    ->description('المعلومات الشخصية الأساسية للطالب')
                    ->components([
                        TextInput::make('name')
                            ->label('الاسم الكامل باللغة العربية')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan('full'),

                        Select::make('gender')
                            ->label('الجنس')
                            ->options([
                                'male' => 'ذكر',
                                'female' => 'أنثى',
                            ])
                            ->required()
                            ->nullable(),

                        DatePicker::make('birth_date')
                            ->label('تاريخ الميلاد')
                            ->maxDate(now()->subYears(5))
                            ->displayFormat('Y-m-d')
                            ->required()
                            ->nullable(),

                        TextInput::make('nationality')
                            ->label('الجنسية')
                            ->required()
                            ->maxLength(100),

                        Textarea::make('address')
                            ->label('العنوان')
                            ->maxLength(500),

                    ])
                    ->columns(1),

                Section::make('المعلومات الأكاديمية')
                    ->description('المعلومات الدراسية والأكاديمية')
                    ->components([
                        TextInput::make('student_id')
                            ->label('الرقم الجامعي')
                            ->unique(User::class, 'student_id')
                            ->maxLength(50)
                            ->placeholder('الرقم الجامعي لطلبة كلية الفقه الحنفي'),

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
                            ->nullable(),

                        Select::make('enrolled_section_ids')
                            ->label('الدبلومات المسجل بها')
                            ->multiple()
                            ->required()
                            ->options(\App\Models\LessonSection::active()->ordered()->pluck('name', 'id'))
                            ->searchable()
                            ->hint('يمكنك اختيار دبلوم واحد أو أكثر ')
                            ->columnSpan('full'),
                    ])
                    ->columns(1),

                Section::make('معلومات الحساب')
                    ->description('معلومات تسجيل الدخول والاتصال')
                    ->components([
                        TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->required()
                            ->unique(User::class, 'email')
                            ->maxLength(255),

                        TextInput::make('username')
                            ->label('اسم المستخدم')
                            ->required()
                            ->unique(User::class, 'username')
                            ->maxLength(255)
                            ->alphaDash(),

                        Select::make('country_code')
                            ->label('رمز الدولة')
                            ->options(User::countryCodeOptions())
                            ->searchable()
                            ->default('+962')
                            ->required(),

                        TextInput::make('phone')
                            ->label('رقم الهاتف')
                            ->placeholder('ادخل رقم الهاتف المربوط بالواتساب')
                            ->tel()
                            ->required()
                            ->maxLength(20),

                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])
                    ->columns(1),

                Section::make('معلومات عامة')
                    ->description('معلومات إضافية اختيارية')
                    ->components([
                        Textarea::make('bio')
                            ->label('نبذة شخصية')
                            ->maxLength(500)
                            ->rows(4),
                    ]),
            ]);
    }

    protected function handleRegistration(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => $data['username'],
            'country_code' => $data['country_code'] ?? '+962',
            'phone' => User::normalizePhoneNumber($data['phone'] ?? null),
            'student_id' => $data['student_id'] ?? null,
            'academic_level' => $data['academic_level'] ?? null,
            'gender' => $data['gender'] ?? null,
            'birth_date' => $data['birth_date'] ?? null,
            'nationality' => $data['nationality'] ?? null,
            'address' => $data['address'] ?? null,
            'bio' => $data['bio'] ?? null,
            'type' => 'student',
            'is_active' => true,
            'password' => $data['password'],  // اترك الـ cast يشفرها (بدون Hash::make!)
        ]);

        // ربط الأقسام المختارة بالطالب في Pivot مع حالة نشطة
        $sectionIds = $data['enrolled_section_ids'] ?? [];
        if (! empty($sectionIds) && is_array($sectionIds)) {
            foreach ($sectionIds as $sectionId) {
                $user->enrolledSections()->attach($sectionId, [
                    'enrolled_at' => now(),
                    'enrollment_status' => 'active',
                    'notes' => null,
                ]);
            }
        }

        return $user;
    }

    protected function getRedirectUrl(): string
    {
        return StudentDashboard::getUrl();
    }
}
