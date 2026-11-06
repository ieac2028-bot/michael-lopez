<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->string('budget_range')->nullable();
            $table->string('consultation_method')->nullable();
            $table->json('recommended_attorney_ids')->nullable();
        });

        Schema::table('attorneys', function (Blueprint $table) {
            $table->unsignedInteger('budget_min')->nullable();
            $table->unsignedInteger('budget_max')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('inquiries', fn (Blueprint $table) => $table->dropColumn([
            'budget_range', 'consultation_method', 'recommended_attorney_ids',
        ]));

        Schema::table('attorneys', fn (Blueprint $table) => $table->dropColumn(['budget_min', 'budget_max']));
    }
};
