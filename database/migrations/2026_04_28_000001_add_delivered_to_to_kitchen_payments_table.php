<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kitchen_payments', function (Blueprint $table) {
            $table->foreignId('delivered_to')
                ->nullable()
                ->after('collected_by')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('kitchen_payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('delivered_to');
        });
    }
};
