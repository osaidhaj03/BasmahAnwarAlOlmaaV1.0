<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kitchen_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->nullable()->constrained('kitchen_invoices')->onDelete('set null');
            $table->foreignId('subscription_id')->constrained('kitchen_subscriptions')->onDelete('cascade');
            $table->decimal('amount', 10, 2); // المبلغ المدفوع
            $table->date('payment_date'); // تاريخ الدفع
            $table->foreignId('collected_by')->nullable()->constrained('users')->onDelete('set null'); // المحصّل
            $table->enum('payment_method', ['cash', 'bank_transfer', 'credit_balance'])->default('cash'); // طريقة الدفع
            $table->text('notes')->nullable(); // ملاحظات
            $table->timestamps();

            // Indexes
            $table->index('invoice_id');
            $table->index('subscription_id');
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kitchen_payments');
    }
};
