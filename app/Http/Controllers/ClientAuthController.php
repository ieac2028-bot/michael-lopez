<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\RecoveryCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ClientAuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectAuthenticatedUser();
        }

        return view('auth.client-login');
    }

    public function login(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectAuthenticatedUser();
        }

        $data = $request->validate([
            'email' => ['required', 'email', 'max:150'],
            'password' => ['required', 'string', 'max:255'],
            'remember' => ['sometimes', 'boolean'],
        ]);
        $credentials = [
            'email' => Str::lower($data['email']),
            'password' => $data['password'],
        ];

        if (! Auth::attempt($credentials, $request->boolean('remember')) || ! Auth::user()?->isClient()) {
            if (Auth::check()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return back()->withErrors(['email' => 'These credentials do not match a client account.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('client-portal'));
    }

    public function showRegistration(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectAuthenticatedUser();
        }

        return view('auth.client-register');
    }

    public function register(Request $request, RecoveryCodeService $recoveryCodes): RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectAuthenticatedUser();
        }

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
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
        ]);

        [$user, $codes] = DB::transaction(function () use ($data, $recoveryCodes): array {
            $user = User::create([
                'name' => $data['name'],
                'email' => Str::lower($data['email']),
                'password' => $data['password'],
                'is_admin' => false,
            ]);

            return [$user, $recoveryCodes->issue($user)];
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('client.recovery-codes')->with('recovery_codes', $codes);
    }

    public function showRecoveryCodes(): View
    {
        return view('auth.recovery-codes');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function redirectAuthenticatedUser(): RedirectResponse
    {
        $user = Auth::user();
        $destination = $user->isAdministrator()
            ? route('admin.dashboard')
            : ($user->isAttorney()
                ? route('attorney.dashboard')
                : route('client-portal'));

        return redirect()->intended($destination);
    }

}
