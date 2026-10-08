<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            // 'general' = submitted via the Contact page, 'consultation' = submitted via Book a Consultation
            $table->string('type')->default('general')->after('status');
            $table->string('phone')->nullable()->after('email');
            $table->date('preferred_date')->nullable()->after('message');
            $table->string('preferred_time')->nullable()->after('preferred_date'); // e.g. "Morning (9–12)"
        });
    }

    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropColumn(['type', 'phone', 'preferred_date', 'preferred_time']);
        });
    }
};
