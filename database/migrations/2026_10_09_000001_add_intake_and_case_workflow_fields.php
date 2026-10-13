<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->string('client_type')->nullable();
            $table->string('client_location')->nullable();
            $table->text('legal_profile')->nullable();
            $table->text('client_requirements')->nullable();
            $table->foreignId('attorney_id')->nullable()->constrained()->nullOnDelete();
        });

        Schema::table('attorneys', function (Blueprint $table) {
            $table->boolean('is_available')->default(true);
            $table->json('coverage_areas')->nullable();
        });

        Schema::table('case_records', function (Blueprint $table) {
            $table->foreignId('inquiry_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->foreignId('practice_area_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('active');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->string('outcome')->nullable();
            $table->text('testimony')->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('is_admin'));

        Schema::table('case_records', function (Blueprint $table) {
            $table->dropConstrainedForeignId('inquiry_id');
            $table->dropConstrainedForeignId('practice_area_id');
            $table->dropColumn(['status', 'submitted_at', 'accepted_at', 'resolved_at', 'outcome', 'testimony']);
        });

        Schema::table('attorneys', fn (Blueprint $table) => $table->dropColumn(['is_available', 'coverage_areas']));

        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('attorney_id');
            $table->dropColumn(['client_type', 'client_location', 'legal_profile', 'client_requirements']);
        });
    }
};
