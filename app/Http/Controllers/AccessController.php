<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AccessController extends Controller
{
    public function __invoke(): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();
            $destination = $user->isAdministrator()
                ? route('admin.dashboard')
                : ($user->isAttorney()
                    ? ($user->hasVerifiedEmail() ? route('attorney.dashboard') : route('verification.notice'))
                    : ($user->hasVerifiedEmail() ? route('client-portal') : route('verification.notice')));

            return redirect()->intended($destination);
        }

        return view('pages.login');
    }
}
