<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KitchenPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'subscription_id',
        'amount',
        'payment_date',
        'collected_by',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    /**
     * Payment method enumeration
     * - cash: نقد
     * - bank_transfer: تحويل بنكي
     * - credit_balance: خصم من الرصيد المتاح
     */
    public const PAYMENT_METHODS = [
        'cash' => 'نقد',
        'bank_transfer' => 'تحويل بنكي',
        'credit_balance' => 'خصم من الرصيد المتاح',
    ];

    // العلاقات

    /**
     * الفاتورة
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(KitchenInvoice::class, 'invoice_id');
    }

    /**
     * الاشتراك
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(KitchenSubscription::class, 'subscription_id');
    }

    /**
     * المحصّل
     */
    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    // Accessors

    /**
     * ترجمة طريقة الدفع
     */
    public function getPaymentMethodArabicAttribute(): string
    {
        return match($this->payment_method) {
            'cash' => 'نقداً',
            'bank_transfer' => 'تحويل بنكي',
            default => $this->payment_method,
        };
    }

    // العلاقات الجديدة

    /**
     * توزيعات الدفعة على الفواتير
     */
    public function allocations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PaymentInvoiceAllocation::class, 'payment_id');
    }

    // Methods

    /**
     * توزيع الدفعة على الفواتير بنظام FIFO
     * الأقدم أولاً
     */
    public function allocateToInvoices(): void
    {
        // حذف التوزيعات السابقة إن وجدت (لإعادة التوزيع)
        $this->allocations()->delete();

        // جلب الفواتير غير المدفوعة بالكامل مرتبة من الأقدم
        $unpaidInvoices = KitchenInvoice::where('subscription_id', $this->subscription_id)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->orderBy('billing_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $remainingAmount = (float) $this->amount;

        foreach ($unpaidInvoices as $invoice) {
            if ($remainingAmount <= 0) {
                break;
            }

            // المبلغ المتبقي على الفاتورة
            $invoiceRemaining = $invoice->remaining_amount;

            if ($invoiceRemaining <= 0) {
                continue;
            }

            // المبلغ المخصص لهذه الفاتورة
            $allocatedAmount = min($remainingAmount, $invoiceRemaining);

            // إنشاء سجل التوزيع
            PaymentInvoiceAllocation::create([
                'payment_id' => $this->id,
                'invoice_id' => $invoice->id,
                'amount_allocated' => $allocatedAmount,
            ]);

            $remainingAmount -= $allocatedAmount;

            // تحديث حالة الفاتورة
            $invoice->updatePaymentStatus();
        }
    }

    /**
     * حساب إجمالي المستحق على الاشتراك
     */
    public static function getTotalOutstandingForSubscription(int $subscriptionId): float
    {
        return KitchenInvoice::where('subscription_id', $subscriptionId)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->get()
            ->sum(fn ($invoice) => $invoice->remaining_amount);
    }

    /**
     * Boot method لتحديث حالات الفواتير عند حذف الدفعة
     */
    protected static function boot()
    {
        parent::boot();

        // عند حذف الدفعة، نحدث حالات الفواتير المرتبطة
        static::deleting(function ($payment) {
            // جلب جميع الفواتير المرتبطة بهذه الدفعة
            $invoiceIds = $payment->allocations()->pluck('invoice_id')->unique();
            
            // حذف التوزيعات أولاً (سيتم تفعيل event listener للتوزيع)
            $payment->allocations()->delete();
            
            // تحديث حالة كل فاتورة مرتبطة
            KitchenInvoice::whereIn('id', $invoiceIds)->get()->each(function ($invoice) {
                $invoice->updatePaymentStatus();
            });
        });
    }
}
