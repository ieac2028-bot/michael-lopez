<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('account_recovery_codes', 'code_type')) {
            Schema::table('account_recovery_codes', function (Blueprint $table): void {
                $table->string('code_type', 20)->default('backup');
            });
        }

        if (! Schema::hasColumn('account_recovery_codes', 'expires_at')) {
            Schema::table('account_recovery_codes', function (Blueprint $table): void {
                $table->timestamp('expires_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        $columns = array_values(array_filter(
            ['code_type', 'expires_at'],
            fn (string $column): bool => Schema::hasColumn('account_recovery_codes', $column)
        ));

        if ($columns !== []) {
            Schema::table('account_recovery_codes', fn (Blueprint $table) => $table->dropColumn($columns));
        }
    }
};
