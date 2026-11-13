<?php

namespace App\Http\Controllers;

use App\Models\Attorney;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
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
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
        ]);

        if ($attorney->account()->exists()) {
            return back()->withErrors(['attorney_account' => 'This attorney already has a login account.']);
        }

        $user = new User([
            'name' => $attorney->name,
            'email' => Str::lower($data['email']),
            'password' => $data['password'],
            'is_admin' => false,
        ]);
        $user->forceFill(['role' => 'attorney', 'attorney_id' => $attorney->id]);
        $user->save();

        return back()->with('admin_status', 'Attorney login account created. Share the initial password securely with the attorney.');
    }
}
