<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For MySQL, we need to change the enum to include 'credit_balance'
        Schema::table('kitchen_payments', function (Blueprint $table) {
            $table->enum('payment_method', ['cash', 'bank_transfer', 'credit_balance'])
                ->change()
                ->default('cash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kitchen_payments', function (Blueprint $table) {
            $table->enum('payment_method', ['cash', 'bank_transfer'])
                ->change()
                ->default('cash');
        });
    }
};
