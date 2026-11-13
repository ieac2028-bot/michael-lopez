<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Soft "off switch" for staff accounts: keeps the record and audit trail, removes access.
            $table->timestamp('deactivated_at')->nullable();
        });

        // Administrators that already exist were created by hand and can sign in, so mark them as
        // set up. This keeps them from showing as "Setup link sent" in the new admin list.
        DB::table('users')
            ->where(fn ($query) => $query->where('role', 'admin')->orWhere('is_admin', true))
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('deactivated_at');
        });
    }
};
