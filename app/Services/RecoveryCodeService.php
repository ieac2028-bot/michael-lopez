<?php

namespace App\Services;

use App\Models\AccountRecoveryCode;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RecoveryCodeService
{
    private const ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    private const CODE_LENGTH = 20;
    private const PREFIX_LENGTH = 8;

    /** @return list<string> Plaintext codes are returned once and never stored. */
    public function issue(User $user, int $count = 8): array
    {
        return DB::transaction(function () use ($user, $count): array {
            $user->recoveryCodes()->where('code_type', 'backup')->whereNull('used_at')->delete();

            $codes = [];
            for ($i = 0; $i < $count; $i++) {
                $plainCode = $this->generateCode();
                $normalized = $this->normalize($plainCode);
                $user->recoveryCodes()->create([
                    'code_prefix' => substr($normalized, 0, self::PREFIX_LENGTH),
                    'code_hash' => $this->digest($normalized),
                    'code_type' => 'backup',
                ]);
                $codes[] = $plainCode;
            }

            return $codes;
        });
    }

    public function issueEmailResetCode(User $user): string
    {
        return DB::transaction(function () use ($user): string {
            $user->recoveryCodes()
                ->where('code_type', 'email')
                ->whereNull('used_at')
                ->delete();

            $plainCode = $this->generateCode();
            $normalized = $this->normalize($plainCode);
            $user->recoveryCodes()->create([
                'code_prefix' => substr($normalized, 0, self::PREFIX_LENGTH),
                'code_hash' => $this->digest($normalized),
                'code_type' => 'email',
                'expires_at' => now()->addMinutes(15),
            ]);

            return $plainCode;
        });
    }

    public function findMatchingUnusedCode(User $user, string $plainCode): ?AccountRecoveryCode
    {
        $normalized = $this->normalize($plainCode);
        if (strlen($normalized) !== self::CODE_LENGTH) {
            return null;
        }

        $expectedDigest = $this->digest($normalized);

        return $user->recoveryCodes()
            ->whereNull('used_at')
            ->where(function ($query): void {
                $query->where('code_type', 'backup')
                    ->orWhere(function ($query): void {
                        $query->where('code_type', 'email')->where('expires_at', '>', now());
                    });
            })
            ->where('code_prefix', substr($normalized, 0, self::PREFIX_LENGTH))
            ->get()
            ->first(fn (AccountRecoveryCode $code) => hash_equals($code->code_hash, $expectedDigest));
    }

    private function generateCode(): string
    {
        $raw = '';
        for ($i = 0; $i < self::CODE_LENGTH; $i++) {
            $raw .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
        }

        return implode('-', str_split($raw, 4));
    }

    private function normalize(string $code): string
    {
        return Str::upper((string) preg_replace('/[^A-Z0-9]/i', '', $code));
    }

    private function digest(string $normalized): string
    {
        return hash_hmac('sha256', $normalized, (string) config('app.key'));
    }
}
