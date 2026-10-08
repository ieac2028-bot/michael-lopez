<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attorneys', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('name');
            $table->decimal('sample_rating', 2, 1)->nullable()->after('specialization_note');
            $table->date('sample_last_practiced_on')->nullable()->after('sample_rating');
        });
    }

    public function down(): void
    {
        Schema::table('attorneys', function (Blueprint $table) {
            $table->dropColumn(['photo_path', 'sample_rating', 'sample_last_practiced_on']);
        });
    }
};
