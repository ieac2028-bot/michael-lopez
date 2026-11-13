<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attorneys', function (Blueprint $table) {
            $table->json('additional_practice_area_ids')->nullable();
            $table->json('spoken_languages')->nullable();
        });

        \Illuminate\Support\Facades\DB::table('attorneys')
            ->whereNull('spoken_languages')
            ->update(['spoken_languages' => json_encode(['English', 'Filipino/Tagalog'])]);
    }

    public function down(): void
    {
        Schema::table('attorneys', function (Blueprint $table) {
            $table->dropColumn(['additional_practice_area_ids', 'spoken_languages']);
        });
    }
};
