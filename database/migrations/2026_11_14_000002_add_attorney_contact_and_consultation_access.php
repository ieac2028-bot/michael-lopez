<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attorneys', function (Blueprint $table) {
            $table->string('contact_email', 150)->nullable();
            $table->string('contact_phone', 40)->nullable();
        });

        Schema::table('inquiries', function (Blueprint $table) {
            $table->string('meeting_access_url', 500)->nullable();
            $table->text('consultation_access_details')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropColumn(['meeting_access_url', 'consultation_access_details']);
        });

        Schema::table('attorneys', function (Blueprint $table) {
            $table->dropColumn(['contact_email', 'contact_phone']);
        });
    }
};
