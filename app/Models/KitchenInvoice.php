<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KitchenInvoice extends Model
{
    use HasFactory;

    protected $appends = [
        'billing_period',
    ];

    protected $fillable = [
        'subscription_id',
        'user_id',
        'invoice_number',
        'amount',
        'billing_date',
        'due_date',
        'status',
        'collected_by',
        'received_from',
        'paid_at',
        'payment_method',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'billing_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    // العلاقات

    /**
     * الاشتراك
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(KitchenSubscription::class, 'subscription_id');
    }

    /**
     * الزبون
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * المستلم (طباخ أو مدير)
     */
    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    /**
     * الدفعات المرتبطة بهذه الفاتورة (علاقة قديمة - للتوافق)
     */
    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(KitchenPayment::class, 'invoice_id');
    }

    /**
     * توزيعات الدفعات على هذه الفاتورة
     */
    public function allocations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PaymentInvoiceAllocation::class, 'invoice_id');
    }

    /**
     * مجموع الدفعات للفاتورة (من جدول التوزيعات)
     */
    public function getTotalPaidAttribute(): float
    {
        return (float) $this->allocations()->sum('amount_allocated');
    }

    /**
     * المبلغ المتبقي
     */
    public function getRemainingAmountAttribute(): float
    {
        return (float) ($this->amount - $this->total_paid);
    }

    // Accessors

    /**
     * ترجمة الحالة
     */
    public function getStatusArabicAttribute(): string
    {
        return match($this->status) {
            'pending' => 'قيد الانتظار',
            'partial' => 'مدفوعة جزئياً',
            'paid' => 'مدفوعة',
            'overdue' => 'متأخرة',
            'cancelled' => 'ملغية',
            default => $this->status,
        };
    }

    /**
     * ترجمة طريقة الدفع
     */
    public function getPaymentMethodArabicAttribute(): ?string
    {
        if (!$this->payment_method) return null;

        return match($this->payment_method) {
            'cash' => 'نقداً',
            'bank_transfer' => 'تحويل بنكي',
            default => $this->payment_method,
        };
    }

    /**
     * هل الفاتورة مدفوعة؟
     */
    public function getIsPaidAttribute(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * هل الفاتورة متأخرة؟
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->status !== 'paid' && $this->due_date < today();
    }

    /**
     * فترة الفاتورة المحسوبة من تاريخ الفوترة.
     * المثال: 25/1 إلى 25/2 لفاتورة شهر 2.
     */
    public function getBillingPeriodStartAttribute(): Carbon
    {
        $billingDate = $this->billing_date instanceof Carbon
            ? $this->billing_date->copy()
            : Carbon::parse($this->billing_date);

        $start = $billingDate->copy()->day(25)->startOfDay();

        if ($billingDate->day < 25) {
            $start->subMonthNoOverflow();
        }

        return $start;
    }

    public function getBillingPeriodEndAttribute(): Carbon
    {
        return $this->billing_period_start->copy()->addMonthNoOverflow()->startOfDay();
    }

    public function getBillingPeriodAttribute(): string
    {
        return sprintf(
            'شهر %s (%s - %s)',
            $this->billing_period_end->format('n'),
            $this->billing_period_start->format('d/m'),
            $this->billing_period_end->format('d/m')
        );
    }

    // Scopes

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['pending', 'partial', 'overdue']);
    }

    /**
     * تحديث حالة الدفع للفاتورة بناءً على المبالغ المدفوعة
     */
    public function updatePaymentStatus(): void
    {
        $this->refresh(); // إعادة تحميل البيانات
        
        $totalPaid = $this->total_paid;
        $amount = (float) $this->amount;

        if ($totalPaid >= $amount) {
            $this->status = 'paid';
            $this->paid_at = now();
        } elseif ($totalPaid > 0) {
            $this->status = 'partial';
        } elseif ($this->due_date < today() && $this->status !== 'cancelled') {
            $this->status = 'overdue';
        }
        
        $this->save();
    }

    // Methods

    /**
     * توليد رقم فاتورة جديد
     */
    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV-' . date('Ym');
        $lastInvoice = static::where('invoice_number', 'like', $prefix . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Boot method للتحقق قبل الحذف
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($invoice) {
            if ($invoice->allocations()->exists()) {
                throw new \Exception('لا يمكن حذف فاتورة مرتبطة بسند قبض. يرجى حذف سند القبض أولاً.');
            }
        });
    }
}
