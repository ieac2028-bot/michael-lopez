<?php

namespace App\Http\Controllers;

use App\Models\CaseRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttorneyAuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->isAttorney() ? 'attorney.dashboard' : 'login');
        }

        return view('auth.attorney-login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:150'],
            'password' => ['required', 'string', 'max:255'],
            'remember' => ['sometimes', 'boolean'],
        ]);

        if (! Auth::attempt(['email' => mb_strtolower($data['email']), 'password' => $data['password']], $request->boolean('remember'))
            || ! Auth::user()?->isAttorney()) {
            if (Auth::check()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return back()->withErrors(['email' => 'These credentials do not match an attorney account.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('attorney.dashboard'));
    }

    public function dashboard(Request $request): View
    {
        $cases = CaseRecord::query()
            ->where('attorney_id', $request->user()->attorney_id)
            ->with('practiceArea')
            ->latest('accepted_at')
            ->get();

        return view('attorney.dashboard', compact('cases'));
    }

    public function updateCase(Request $request, CaseRecord $caseRecord): RedirectResponse
    {
        abort_unless($caseRecord->attorney_id === $request->user()->attorney_id, 404);

        $data = $request->validate([
            'status' => ['required', Rule::in(['active', 'on_hold', 'resolved', 'closed'])],
        ]);

        $timeline = $caseRecord->timeline ?? [];
        foreach ($timeline as &$step) {
            if (($step['state'] ?? null) === 'current') {
                $step['state'] = 'done';
            }
        }
        unset($step);

        if ($caseRecord->status !== $data['status']) {
            $labels = [
                'active' => 'Matter in progress',
                'on_hold' => 'Matter on hold',
                'resolved' => 'Matter resolved',
                'closed' => 'Matter closed',
            ];
            $timeline[] = [
                'label' => $labels[$data['status']],
                'date' => now()->toDateString(),
                'state' => in_array($data['status'], ['resolved', 'closed'], true) ? 'done' : 'current',
            ];
        }

        $caseRecord->update([
            'status' => $data['status'],
            'timeline' => $timeline,
            'resolved_at' => in_array($data['status'], ['resolved', 'closed'], true)
                ? ($caseRecord->resolved_at ?? now())
                : null,
        ]);

        return back()->with('attorney_status', 'Case progress was updated.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('attorney.login');
    }
}
