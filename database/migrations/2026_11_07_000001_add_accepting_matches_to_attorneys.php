<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attorneys', function (Blueprint $table) {
            $table->boolean('accepting_matches')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('attorneys', function (Blueprint $table) {
            $table->dropColumn('accepting_matches');
        });
    }
};
