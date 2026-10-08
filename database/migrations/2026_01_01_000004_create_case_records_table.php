<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_records', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->string('client_email');
            $table->string('title');
            $table->foreignId('attorney_id')->nullable()->constrained()->nullOnDelete();
            $table->json('timeline');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_records');
    }
};
