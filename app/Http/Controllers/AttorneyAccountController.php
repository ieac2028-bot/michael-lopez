<?php

namespace App\Http\Controllers;

use App\Models\Attorney;
use App\Models\User;
use App\Services\RecoveryCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;

class AttorneyAccountController extends Controller
{
    public function store(Request $request, Attorney $attorney, RecoveryCodeService $recoveryCodes): RedirectResponse
    {
        $data = $request->validate([
            'display_name' => ['required', 'string', 'max:150'],
            'attorney_email' => [
                'required', 'email', 'max:150',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (User::query()->whereRaw('LOWER(email) = ?', [Str::lower($value)])->exists()) {
                        $fail('An account already exists for this email address.');
                    }
                },
            ],
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
        ]);

        if ($attorney->account()->exists()) {
            return back()->withErrors(['attorney_account' => 'This attorney already has a login account.']);
        }

        $user = new User([
            'name' => trim($data['display_name']),
            'email' => Str::lower($data['attorney_email']),
            'password' => $data['password'],
            'is_admin' => false,
        ]);
        $user->forceFill(['role' => 'attorney', 'attorney_id' => $attorney->id]);
        $user->save();
        $codes = $recoveryCodes->issue($user);

        return back()
            ->with('admin_status', 'Attorney login created. Share the initial password and recovery codes securely with the attorney.')
            ->with('attorney_recovery_email', $user->email)
            ->with('attorney_recovery_codes', $codes);
    }
}
