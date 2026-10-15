<?php

namespace App\Http\Controllers;

use App\Models\Attorney;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AttorneyAccountController extends Controller
{
    public function store(Request $request, Attorney $attorney): RedirectResponse
    {
        $data = $request->validate([
            'email' => [
                'required', 'email', 'max:150',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (User::query()->whereRaw('LOWER(email) = ?', [Str::lower($value)])->exists()) {
                        $fail('An account already exists for this email address.');
                    }
                },
            ],
        ]);

        if ($attorney->account()->exists()) {
            return back()->withErrors(['attorney_account' => 'This attorney already has a login account.']);
        }

        $user = new User([
            'name' => $attorney->name,
            'email' => Str::lower($data['email']),
            'password' => Hash::make(Str::random(64)),
            'is_admin' => false,
        ]);
        $user->forceFill(['role' => 'attorney', 'attorney_id' => $attorney->id]);
        $user->save();

        try {
            $status = Password::sendResetLink(['email' => $user->email]);
        } catch (\Throwable $exception) {
            report($exception);
            $user->delete();

            return back()->withErrors(['attorney_account' => 'The invitation email could not be sent. Check mail configuration and try again.']);
        }

        if ($status !== Password::RESET_LINK_SENT) {
            $user->delete();
            return back()->withErrors(['attorney_account' => 'The invitation email could not be sent. Check mail configuration and try again.']);
        }

        return back()->with('admin_status', 'Attorney account created. A secure password setup link was emailed to '.$user->email.'.');
    }
}
