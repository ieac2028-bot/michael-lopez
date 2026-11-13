<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_recovery_codes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('code_prefix', 8);
            $table->char('code_hash', 64);
            $table->string('code_type', 20)->default('backup');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'code_type', 'used_at', 'expires_at', 'code_prefix']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_recovery_codes');
    }
};
