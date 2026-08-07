<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
            $table->string('country_code', 8)->nullable()->after('type');

            $table->index(['country_code', 'phone']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['country_code', 'phone']);
            $table->dropColumn(['username', 'country_code']);
        });
    }
};
