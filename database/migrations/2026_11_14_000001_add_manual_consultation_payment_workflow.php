<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->decimal('consultation_fee', 12, 2)->nullable();
            $table->decimal('proposed_consultation_fee', 12, 2)->nullable();
            $table->string('payment_status', 32)->nullable()->index();
            $table->text('payment_instructions')->nullable();
            $table->text('payment_refund_terms')->nullable();
            $table->foreignId('fee_proposed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fee_proposed_at')->nullable();
            $table->foreignId('fee_confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fee_confirmed_at')->nullable();
            $table->timestamp('payment_expires_at')->nullable();
            $table->timestamp('payment_requested_at')->nullable();
            $table->timestamp('payment_terms_accepted_at')->nullable();
            $table->string('payment_reference', 120)->nullable();
            $table->foreignId('payment_verification_started_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('payment_verification_started_at')->nullable();
            $table->foreignId('payment_verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('payment_verified_at')->nullable();
            $table->timestamp('payment_canceled_at')->nullable();
            $table->timestamp('payment_expired_at')->nullable();
            $table->timestamp('refund_requested_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropForeign(['fee_proposed_by']);
            $table->dropForeign(['fee_confirmed_by']);
            $table->dropForeign(['payment_verification_started_by']);
            $table->dropForeign(['payment_verified_by']);
            $table->dropIndex(['payment_status']);
            $table->dropColumn([
                'consultation_fee', 'proposed_consultation_fee', 'payment_status', 'payment_instructions', 'payment_refund_terms', 'fee_proposed_by', 'fee_proposed_at',
                'fee_confirmed_by', 'fee_confirmed_at', 'payment_expires_at', 'payment_requested_at',
                'payment_terms_accepted_at',
                'payment_reference', 'payment_verification_started_by', 'payment_verification_started_at',
                'payment_verified_by', 'payment_verified_at', 'payment_canceled_at', 'payment_expired_at',
                'refund_requested_at',
            ]);
        });
    }
};
