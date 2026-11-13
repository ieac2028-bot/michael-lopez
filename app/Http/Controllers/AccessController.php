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
                    ? route('attorney.dashboard')
                    : route('client-portal'));

            return redirect()->intended($destination);
        }

        return view('pages.login');
    }
}
