<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\RecoveryCodeService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class IssueAccountRecoveryCodes extends Command
{
    protected $signature = 'account:recovery-codes {email : Email address for the account}';

    protected $description = 'Issue one-time password recovery codes for a verified account holder';

    public function handle(RecoveryCodeService $recoveryCodes): int
    {
        $email = Str::lower(trim((string) $this->argument('email')));
        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

        if (! $user) {
            $this->error('No account was found for that email address.');

            return self::FAILURE;
        }

        $this->warn('Verify the account holder’s identity through a trusted channel before giving them these codes.');
        $codes = $recoveryCodes->issue($user);
        $this->info('New recovery codes for '.$user->email.' (each can be used once):');
        foreach ($codes as $code) {
            $this->line($code);
        }

        return self::SUCCESS;
    }
}
