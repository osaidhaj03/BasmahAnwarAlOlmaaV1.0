<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KitchenSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_number',
        'user_id',
        'kitchen_id',
        'start_date',
        'end_date',
        'status',
        'monthly_price',
        'number_meal',
        'notes',
        // لا نخزن credit_balance - يحسب ديناميكياً
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'monthly_price' => 'decimal:2',
    ];

    /**
     * Boot method لإضافة دور الزبون تلقائياً
     */
    protected static function boot()
    {
        parent::boot();

        // عند إنشاء اشتراك جديد، أضف دور الزبون للمستخدم
        static::created(function ($subscription) {
            $user = $subscription->user;
            $customerRole = Role::where('slug', 'customer')->first();
            
            if ($user && $customerRole && !$user->hasRole('customer')) {
                $user->roles()->attach($customerRole);
            }
        });
    }

    /**
     * الرصيد المتاح (حساب ديناميكي)
     * الرصيد = مجموع الدفعات - مجموع الفواتير
     * موجب = للمشترك رصيد زائد
     * صفر أو سالب = لا يوجد رصيد متاح
     */
    public function getAvailableCreditAttribute(): float
    {
        $balance = $this->balance; // يستخدم getBalanceAttribute()
        return $balance > 0 ? $balance : 0;
    }

    /**
     * توليد رقم اشتراك جديد
     * الصيغة: SUB-YYYYMM-0001
     */
    public static function generateSubscriptionNumber(): string
    {
        $prefix = 'SUB-' . date('Ym');
        $lastSubscription = static::where('subscription_number', 'like', $prefix . '%')
            ->orderBy('subscription_number', 'desc')
            ->first();

        if ($lastSubscription) {
            $lastNumber = (int) substr($lastSubscription->subscription_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    // العلاقات

    /**
     * الزبون
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * المطبخ
     */
    public function kitchen(): BelongsTo
    {
        return $this->belongsTo(Kitchen::class);
    }

    /**
     * سجلات التسليم
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(MealDelivery::class, 'subscription_id');
    }

    /**
     * الفواتير
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(KitchenInvoice::class, 'subscription_id');
    }

    /**
     * الدفعات
     */
    public function payments(): HasMany
    {
        return $this->hasMany(KitchenPayment::class, 'subscription_id');
    }

    /**
     * حساب الرصيد
     * موجب = للمشترك رصيد زائد
     * سالب = عليه متأخرات
     */
    public function getBalanceAttribute(): float
    {
        $totalInvoices = $this->invoices()->sum('amount');
        $totalPayments = $this->payments()->sum('amount');
        
        return (float) ($totalPayments - $totalInvoices);
    }

    /**
     * مجموع الفواتير
     */
    public function getTotalInvoicesAttribute(): float
    {
        return (float) $this->invoices()->sum('amount');
    }

    /**
     * مجموع الدفعات
     */
    public function getTotalPaymentsAttribute(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    // Accessors

    /**
     * ترجمة الحالة
     */
    public function getStatusArabicAttribute(): string
    {
        return match($this->status) {
            'active' => 'فعال',
            'paused' => 'متوقف',
            'cancelled' => 'ملغي',
            'expired' => 'منتهي',
            default => $this->status,
        };
    }

    /**
     * هل الاشتراك نشط؟
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePaused($query)
    {
        return $query->where('status', 'paused');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    /**
     * التحقق مما إذا كان للمستخدم اشتراك فعال آخر
     */
    public static function hasActiveSubscription($userId, $excludeId = null): bool
    {
        return static::where('user_id', $userId)
            ->where('status', 'active')
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists();
    }
}
