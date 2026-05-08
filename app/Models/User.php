<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Jeffgreco13\FilamentBreezy\Traits\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasFactory, Notifiable, HasApiTokens, TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
        'kitchen_id',
        'phone',
        'student_id',
        'academic_level',
        'employee_id',
        'specialization',
        'hire_date',
        'bio',
        'birth_date',
        'gender',
        'address',
        'nationality',
        'is_active',
        'last_login_at',
        'avatar_url',
        'two_factor_confirmed_at',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'birth_date' => 'date',
        'hire_date' => 'date',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
        'two_factor_confirmed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            // Force new registered users to be students unless explicitly set
            if (empty($user->type)) {
                $user->type = 'student';
            }
        });
    }

    // Filament Access Control
    public function canAccessPanel(Panel $panel): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Admin panel - للمدراء والمعلمين فقط
        if ($panel->getId() === 'admin') {
            // دعم مزدوج: الأدوار الجديدة أو type القديم
            return $this->hasRole(['admin', 'teacher']) || in_array($this->type, ['admin', 'teacher']);
        }

        // Student panel - للطلاب والزبائن
        if (in_array($panel->getId(), ['lessons-admin', 'kitchen-admin'], true)) {
            return $this->hasRole('admin') || $this->type === 'admin';
        }

        if ($panel->getId() === 'student') {
            return $this->hasRole(['student', 'customer']) || in_array($this->type, ['student', 'customer']);
        }

        // Cook panel - للطباخين فقط
        if ($panel->getId() === 'cook') {
            return $this->hasRole('cook') || $this->type === 'cook';
        }

        return false;
    }

    // العلاقات
    public function teacherLessons()
    {
        return $this->hasMany(Lesson::class, 'teacher_id');
    }

    public function studentLessons()
    {
        return $this->belongsToMany(Lesson::class, 'lesson_student', 'student_id', 'lesson_id')
                    ->withPivot('enrolled_at', 'enrollment_status', 'notes')
                    ->withTimestamps();
    }

    public function enrolledSections()
    {
        return $this->belongsToMany(LessonSection::class, 'lesson_section_student', 'student_id', 'lesson_section_id')
                    ->withPivot('enrolled_at', 'enrollment_status', 'notes')
                    ->withTimestamps();
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'student_id');
    }

    public function createdAttendanceCodes()
    {
        return $this->hasMany(AttendanceCode::class, 'created_by');
    }

    public function deactivatedAttendanceCodes()
    {
        return $this->hasMany(AttendanceCode::class, 'deactivated_by');
    }

    public function markedAttendances()
    {
        return $this->hasMany(Attendance::class, 'marked_by');
    }

    // علاقات نظام المطبخ

    /**
     * المطبخ الذي يعمل به الطباخ
     */
    public function kitchen()
    {
        return $this->belongsTo(Kitchen::class);
    }

    /**
     * المطابخ التي يعمل بها كطباخ
     */
    public function kitchens()
    {
        return $this->belongsToMany(Kitchen::class, 'kitchen_cooks', 'user_id', 'kitchen_id')
            ->withPivot('specialty', 'is_active')
            ->withTimestamps();
    }

    /**
     * سجلات العمل في المطابخ
     */
    public function kitchenCookRecords()
    {
        return $this->hasMany(KitchenCook::class);
    }

    /**
     * اشتراكات الطعام (كزبون)
     */
    public function kitchenSubscriptions()
    {
        return $this->hasMany(KitchenSubscription::class);
    }

    /**
     * الوجبات المستلمة (كزبون)
     */
    public function mealDeliveries()
    {
        return $this->hasMany(MealDelivery::class);
    }

    /**
     * الوجبات التي سلّمها (كطباخ)
     */
    public function deliveredMeals()
    {
        return $this->hasMany(MealDelivery::class, 'delivered_by');
    }

    /**
     * الفواتير (كزبون)
     */
    public function kitchenInvoices()
    {
        return $this->hasMany(KitchenInvoice::class);
    }

    /**
     * الفواتير المستلمة (كطباخ أو مدير)
     */
    public function collectedInvoices()
    {
        return $this->hasMany(KitchenInvoice::class, 'collected_by');
    }

    /**
     * مجموعات الزبائن
     */
    public function customerGroups()
    {
        return $this->belongsToMany(CustomerGroup::class, 'customer_group_members', 'user_id', 'group_id')
            ->withTimestamps();
    }

    /**
     * المصروفات التي أنشأها
     */
    public function createdExpenses()
    {
        return $this->hasMany(KitchenExpense::class, 'created_by');
    }

    /**
     * أدوار المستخدم
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->withTimestamps();
    }

    /**
     * التحقق من وجود دور معين
     */
    public function hasRole(string|array $roles): bool
    {
        if (is_string($roles)) {
            return $this->roles()->where('slug', $roles)->exists();
        }
        
        return $this->roles()->whereIn('slug', $roles)->exists();
    }

    // Helper methods
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    public function isTeacher()
    {
        return $this->hasRole('teacher');
    }

    public function isStudent()
    {
        return $this->hasRole('student');
    }

    public function isCustomer()
    {
        return $this->hasRole('customer');
    }

    public function isCook()
    {
        return $this->hasRole('cook');
    }

    /**
     * هل لديه اشتراك طعام نشط؟
     */
    public function hasActiveKitchenSubscription(): bool
    {
        return $this->kitchenSubscriptions()->where('status', 'active')->exists();
    }

    public function getFullNameAttribute()
    {
        return $this->name;
    }

    // Filament Avatar Support
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url ? Storage::url($this->avatar_url) : null;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * حساب غرامة الغياب الشهرية
     * الصيغة: floor((20 / عدد المحاضرات الإجبارية الشهرية) × عدد الغيابات + 10)
     * 
     * @return array ['absence_price' => float, 'absence_count' => int, 'penalty_amount' => int]
     */
    public function calculateAbsencePenalty(): array
    {
        // حساب عدد المحاضرات في الشهر الحالي لجميع الدورات الإجبارية
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        // الحصول على جميع المحاضرات الإجبارية في الشهر الحالي
        $totalMonthlyLectures = \App\Models\Lecture::whereHas('lesson', function ($query) {
                $query->where('is_mandatory', true);
            })
            ->whereMonth('lecture_date', $currentMonth)
            ->whereYear('lecture_date', $currentYear)
            ->count();
        
        // حساب سعر الغياب الواحد
        $absencePrice = $totalMonthlyLectures > 0 ? (20 / $totalMonthlyLectures) : 0;
        
        // حساب عدد المحاضرات التي حضرها الطالب في الدورات الإجبارية
        $attendedLectures = $this->attendances()
            ->whereIn('status', ['present', 'late'])
            ->whereMonth('attendance_date', $currentMonth)
            ->whereYear('attendance_date', $currentYear)
            ->whereHas('lecture.lesson', function ($query) {
                $query->where('is_mandatory', true);
            })
            ->count();
        
        // عدد الغيابات = جميع المحاضرات الإجبارية - المحاضرات التي حضرها
        $absenceCount = $totalMonthlyLectures - $attendedLectures;
        
        // حساب المبلغ النهائي: floor((سعر_الغياب × عدد_الغيابات) + 10)
        $penaltyAmount = $totalMonthlyLectures > 0 
            ? (int) floor(($absencePrice * $absenceCount) + 10)
            : 0;
        
        return [
            'absence_price' => round($absencePrice, 2),
            'absence_count' => $absenceCount,
            'penalty_amount' => $penaltyAmount,
        ];
    }

    /**
     * حساب غرامة الغياب لفترة محددة
     * الصيغة: floor((20 / عدد المحاضرات الإجبارية في الفترة) × عدد الغيابات + 10)
     * 
     * @param string|null $from تاريخ البداية
     * @param string|null $to تاريخ النهاية
     * @return array ['absence_price' => float, 'absence_count' => int, 'penalty_amount' => int, 'total_lectures' => int, 'attended_lectures' => int]
     */
    public function calculateAbsencePenaltyForPeriod(?string $from = null, ?string $to = null): array
    {
        // بناء query للمحاضرات الإجبارية في الفترة المحددة
        $lecturesQuery = \App\Models\Lecture::whereHas('lesson', function ($query) {
            $query->where('is_mandatory', true);
        });
        
        if ($from) {
            $lecturesQuery->whereDate('lecture_date', '>=', $from);
        }
        if ($to) {
            $lecturesQuery->whereDate('lecture_date', '<=', $to);
        }
        
        $totalLectures = $lecturesQuery->count();
        
        // حساب سعر الغياب الواحد
        $absencePrice = $totalLectures > 0 ? (20 / $totalLectures) : 0;
        
        // حساب عدد المحاضرات التي حضرها الطالب في الدورات الإجبارية
        $attendanceQuery = $this->attendances()
            ->whereIn('status', ['present', 'late'])
            ->whereHas('lecture.lesson', function ($query) {
                $query->where('is_mandatory', true);
            });
        
        if ($from || $to) {
            $attendanceQuery->whereHas('lecture', function ($q) use ($from, $to) {
                if ($from) {
                    $q->whereDate('lecture_date', '>=', $from);
                }
                if ($to) {
                    $q->whereDate('lecture_date', '<=', $to);
                }
            });
        }
        
        $attendedLectures = $attendanceQuery->count();
        
        // عدد الغيابات = جميع المحاضرات الإجبارية - المحاضرات التي حضرها
        $absenceCount = $totalLectures - $attendedLectures;
        
        // حساب المبلغ النهائي: floor((سعر_الغياب × عدد_الغيابات) + 10)
        $penaltyAmount = $totalLectures > 0 
            ? (int) floor(($absencePrice * $absenceCount) + 10)
            : 0;
        
        return [
            'absence_price' => round($absencePrice, 2),
            'absence_count' => $absenceCount,
            'penalty_amount' => $penaltyAmount,
            'total_lectures' => $totalLectures,
            'attended_lectures' => $attendedLectures,
        ];
    }
}
