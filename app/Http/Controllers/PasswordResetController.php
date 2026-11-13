<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\GmailApiEmailService;
use App\Services\RecoveryCodeService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;
use Throwable;

class PasswordResetController extends Controller
{
    public function requestForm(): View
    {
        return view('auth.password-email');
    }

    public function sendRecoveryCode(Request $request, RecoveryCodeService $recoveryCodes, GmailApiEmailService $emailService): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:150']]);
        $email = Str::lower(trim($data['email']));
        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

        if ($user) {
            try {
                $code = $recoveryCodes->issueEmailResetCode($user);
                $emailService->sendPasswordRecoveryCode($user, $code);
            } catch (Throwable $exception) {
                Log::warning('Could not send an account recovery email.', [
                    'user_id' => $user->id,
                    'exception' => $exception::class,
                ]);
            }
        }

        return back()->withInput(['email' => $email])->with('status', 'If an account exists for that email, a recovery code has been sent. It expires in 15 minutes. If it does not arrive, use a saved recovery code or contact an administrator.');
    }

    public function reset(Request $request, RecoveryCodeService $recoveryCodes): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:150'],
            'recovery_code' => ['required', 'string', 'max:40'],
            'password' => ['required', 'confirmed', PasswordRule::min(12)->mixedCase()->numbers()->symbols()],
        ]);
        $data['email'] = Str::lower(trim($data['email']));

        $user = DB::transaction(function () use ($data, $recoveryCodes): ?User {
            $user = User::query()
                ->whereRaw('LOWER(email) = ?', [$data['email']])
                ->lockForUpdate()
                ->first();

            if (! $user) {
                return null;
            }

            $recoveryCode = $recoveryCodes->findMatchingUnusedCode($user, $data['recovery_code']);
            if (! $recoveryCode) {
                return null;
            }

            $claimed = $user->recoveryCodes()
                ->whereKey($recoveryCode->getKey())
                ->whereNull('used_at')
                ->update(['used_at' => now(), 'updated_at' => now()]);

            if ($claimed !== 1) {
                return null;
            }

            $user->forceFill(['password' => $data['password']]);
            $user->setRememberToken(Str::random(60));
            $user->save();

            return $user;
        });

        if (! $user) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['recovery_code' => 'The email and recovery code did not match, or that code has already been used.']);
        }

        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
        }

        event(new PasswordReset($user));

        return redirect()->route('login')->with('status', 'Your password has been changed. You can now sign in.');
    }
}
