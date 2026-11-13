<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class CreateAdmin extends Command
{
    protected $signature = 'admin:create
        {--name= : Full name}
        {--email= : Email address}
        {--invite : Email a password setup link instead of typing a password}';

    protected $description = 'Create a GPMO administrator account (used once to create the first admin; after that admins add each other from the dashboard)';

    public function handle(): int
    {
        $name = trim((string) ($this->option('name') ?: $this->ask('Full name')));
        $email = Str::lower(trim((string) ($this->option('email') ?: $this->ask('Email address'))));

        $validator = Validator::make(
            ['name' => $name, 'email' => $email],
            ['name' => ['required', 'string', 'max:150'], 'email' => ['required', 'email', 'max:150']]
        );
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        if (User::query()->whereRaw('LOWER(email) = ?', [$email])->exists()) {
            $this->error('An account with that email already exists. Nothing was changed.');

            return self::FAILURE;
        }

        $invite = (bool) $this->option('invite');

        if ($invite) {
            $password = Str::random(64);
        } else {
            // The password is only ever typed at a hidden prompt, never passed as an option,
            // so it does not end up in shell history or process listings.
            $password = (string) $this->secret('Password (min 12 chars, mixed case, a number and a symbol)');
            $confirmation = (string) $this->secret('Confirm password');

            if ($password !== $confirmation) {
                $this->error('The passwords do not match. Nothing was changed.');

                return self::FAILURE;
            }

            $passwordCheck = Validator::make(
                ['password' => $password],
                ['password' => ['required', PasswordRule::min(12)->mixedCase()->numbers()->symbols()]]
            );
            if ($passwordCheck->fails()) {
                foreach ($passwordCheck->errors()->all() as $message) {
                    $this->error($message);
                }

                return self::FAILURE;
            }
        }

        $user = new User([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'is_admin' => true,
        ]);
        $user->forceFill(['role' => 'admin', 'email_verified_at' => $invite ? null : now()]);
        $user->save();

        if ($invite) {
            try {
                $status = Password::sendResetLink(['email' => $user->email]);
            } catch (\Throwable $exception) {
                report($exception);
                $status = null;
            }

            if ($status !== Password::RESET_LINK_SENT) {
                $user->delete();
                $this->error('The setup email could not be sent. Check the mail settings in .env. Nothing was created.');

                return self::FAILURE;
            }

            $this->info("Administrator {$email} created. A password setup link was emailed to them.");

            return self::SUCCESS;
        }

        $this->info("Administrator {$email} created. Sign in at /login and choose Administrator.");

        return self::SUCCESS;
    }
}
