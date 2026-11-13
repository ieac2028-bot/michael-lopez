<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * Lets a signed-in administrator add, deactivate and reactivate other administrators
 * from the dashboard, so nobody has to edit the database by hand. Every route is behind
 * the existing `auth` + `can:manage-gpmo` middleware; there is no public admin sign-up.
 */
class AdminAccountController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => [
                'required', 'email', 'max:150',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (User::query()->whereRaw('LOWER(email) = ?', [Str::lower($value)])->exists()) {
                        $fail('An account already exists for this email address.');
                    }
                },
            ],
        ]);

        // The creating admin never sees or types a password: the account starts with a random
        // one and the new administrator chooses their own through the emailed setup link.
        $user = new User([
            'name' => trim($data['name']),
            'email' => Str::lower($data['email']),
            'password' => Hash::make(Str::random(64)),
            'is_admin' => true,
        ]);
        $user->forceFill(['role' => 'admin']);
        $user->save();

        $status = $this->sendSetupLink($user);
        if ($status !== Password::RESET_LINK_SENT) {
            $user->delete();

            return $this->fail('The invitation email could not be sent. Check mail configuration and try again.')->withInput();
        }

        return $this->done('Administrator account created. A secure password setup link was emailed to '.$user->email.'.');
    }

    public function deactivate(Request $request, User $admin): RedirectResponse
    {
        abort_unless($admin->hasAdminRole(), 404);

        if ($admin->is($request->user())) {
            return $this->fail('You cannot deactivate your own account.');
        }
        if ($admin->isDeactivated()) {
            return $this->done($admin->email.' is already deactivated.');
        }

        // Re-check inside a transaction so two admins deactivating each other at the same moment
        // can never leave the system with no active administrator.
        $deactivated = DB::transaction(function () use ($admin): bool {
            $others = User::query()->administrators()->active()
                ->where('id', '!=', $admin->id)->lockForUpdate()->get(['id'])->count();
            if ($others === 0) {
                return false;
            }

            $admin->forceFill(['deactivated_at' => now()]);
            $admin->setRememberToken(Str::random(60));
            $admin->save();

            return true;
        });

        if (! $deactivated) {
            return $this->fail('At least one active administrator must remain.');
        }

        // Access checks already fail on the next request; also end any stored sessions when sessions are in the database.
        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))->where('user_id', $admin->id)->delete();
        }

        return $this->done($admin->email.' was deactivated and can no longer sign in.');
    }

    public function reactivate(User $admin): RedirectResponse
    {
        abort_unless($admin->hasAdminRole(), 404);

        $admin->forceFill(['deactivated_at' => null])->save();

        return $this->done($admin->email.' was reactivated.');
    }

    public function resendInvite(User $admin): RedirectResponse
    {
        abort_unless($admin->hasAdminRole(), 404);

        if ($admin->isDeactivated()) {
            return $this->fail('Reactivate this account before sending a new setup link.');
        }
        if ($admin->email_verified_at !== null) {
            return $this->fail('This administrator has already set up their password.');
        }

        $status = $this->sendSetupLink($admin);
        if ($status === Password::RESET_THROTTLED) {
            return $this->fail('A setup link was sent very recently. Please wait a minute and try again.');
        }
        if ($status !== Password::RESET_LINK_SENT) {
            return $this->fail('The invitation email could not be sent. Check mail configuration and try again.');
        }

        return $this->done('A new password setup link was emailed to '.$admin->email.'.');
    }

    /** Returns the broker status, or null if sending threw. */
    private function sendSetupLink(User $user): ?string
    {
        try {
            return Password::sendResetLink(['email' => $user->email]);
        } catch (\Throwable $exception) {
            report($exception);

            return null;
        }
    }

    private function done(string $message): RedirectResponse
    {
        return redirect()->to(route('admin.dashboard').'#admin-accounts')->with('admin_status', $message);
    }

    private function fail(string $message): RedirectResponse
    {
        return redirect()->to(route('admin.dashboard').'#admin-accounts')->withErrors(['admin_account' => $message]);
    }
}
