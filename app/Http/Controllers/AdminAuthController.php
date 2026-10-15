<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function show(): View|RedirectResponse
    {
        if (Auth::check()) {
            if (Auth::user()->isAdministrator()) {
                return redirect()->route('admin.dashboard');
            }

            if (Auth::user()->isAttorney()) {
                return redirect()->route('attorney.dashboard');
            }

            return redirect()->route(Auth::user()->hasVerifiedEmail() ? 'client-portal' : 'verification.notice');
        }

        return view('admin.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ]);
        $credentials['email'] = Str::lower($credentials['email']);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'These credentials do not match an admin account.'])->onlyInput('email');
        }

        $request->session()->regenerate();
        if (! Auth::user()?->isAdministrator()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors(['email' => 'These credentials do not match an admin account.'])->onlyInput('email');
        }

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
