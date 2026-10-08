<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attorneys', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('rank');
            $table->foreignId('practice_area_id')->nullable()->constrained()->nullOnDelete();
            $table->string('specialization_note')->nullable();
            $table->unsignedSmallInteger('years_experience')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attorneys');
    }
};
