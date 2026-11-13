<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->constrained('users')->nullOnDelete();
        });

        $clientIdsByEmail = DB::table('users')
            ->where('role', 'client')
            ->get(['id', 'email'])
            ->mapWithKeys(fn ($user) => [mb_strtolower($user->email) => $user->id]);

        DB::table('inquiries')->select(['id', 'email'])->orderBy('id')->chunk(500, function ($inquiries) use ($clientIdsByEmail) {
            foreach ($inquiries as $inquiry) {
                $clientId = $clientIdsByEmail[mb_strtolower($inquiry->email)] ?? null;
                if ($clientId) {
                    DB::table('inquiries')->where('id', $inquiry->id)->update(['client_id' => $clientId]);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_id');
        });
    }
};
