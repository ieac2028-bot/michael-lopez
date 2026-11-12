<?php

namespace App\Http\Controllers;

use App\Models\CaseRecord;
use App\Models\Inquiry;
use App\Models\User;
use App\Notifications\ConsultationConfirmedNotification;
use App\Services\AttorneyMatchingService;
use App\Models\InquiryMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
        $inquiries = Inquiry::query()
            ->with(['practiceArea', 'messages'])
            ->where('attorney_id', $request->user()->attorney_id)
            ->where('status', 'referred')
            ->latest()
            ->get();
        $inquiries->each(function (Inquiry $inquiry) {
            $clientMessages = $inquiry->messages->where('sender_role', 'client');
            $staffMessages = $inquiry->messages->where('sender_role', 'admin');
            $concern = $inquiry->legal_profile ?: $clientMessages->first()?->body ?: $inquiry->message;
            $details = array_filter([
                $inquiry->client_location ? 'Location: ' . $inquiry->client_location : null,
                $inquiry->nationality ? 'Nationality: ' . $inquiry->nationality : 'Nationality not provided',
                $inquiry->consultation_method ? 'Consultation: ' . str_replace('_', ' ', $inquiry->consultation_method) : null,
                $inquiry->preferred_date ? 'Preferred date: ' . $inquiry->preferred_date->format('M j, Y') : null,
                $inquiry->client_requirements ?: null,
            ]);
            $summary = 'The client is seeking ' . ($inquiry->practiceArea?->name ?? 'legal') . ' assistance';
            if ($concern) {
                $summary .= ' for ' . \Illuminate\Support\Str::limit(\Illuminate\Support\Str::squish($concern), 180);
            }
            if ($details !== []) {
                $summary .= '. Intake details recorded: ' . implode('; ', array_map(
                    fn ($detail) => \Illuminate\Support\Str::limit(\Illuminate\Support\Str::squish($detail), 120),
                    $details
                ));
            }
            $summary .= '. Firm staff has sent ' . $staffMessages->count() . ' follow-up '
                . \Illuminate\Support\Str::plural('message', $staffMessages->count()) . '.';
            $inquiry->setAttribute('conversation_summary', $summary);
        });
        $cases = CaseRecord::query()
            ->where('attorney_id', $request->user()->attorney_id)
            ->with('practiceArea')
            ->latest('accepted_at')
            ->get();

        return view('attorney.dashboard', compact('cases', 'inquiries'));
    }

    public function updateMatchingAvailability(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'accepting_matches' => ['required', 'boolean'],
        ]);
        $attorney = $request->user()->attorneyProfile;
        abort_unless($attorney, 403);
        $attorney->update(['accepting_matches' => (bool) $data['accepting_matches']]);

        return back()->with('attorney_status', $data['accepting_matches']
            ? 'You are included in new attorney matching.'
            : 'You are excluded from new attorney matching. Existing assigned requests and cases are unchanged.');
    }

    public function respondToInquiry(Request $request, Inquiry $inquiry, AttorneyMatchingService $attorneyMatcher): RedirectResponse
    {
        abort_unless($inquiry->attorney_id === $request->user()->attorney_id, 404);
        if ($inquiry->type === Inquiry::TYPE_CONSULTATION && $inquiry->status === 'accepted') {
            $existingCase = CaseRecord::where('inquiry_id', $inquiry->id)->first();
            if ($existingCase) {
                return back()->with('attorney_status', 'This consultation was already confirmed as case ' . $existingCase->reference_no . '.');
            }
        }
        abort_unless($inquiry->type === Inquiry::TYPE_CONSULTATION && $inquiry->status === 'referred', 404);

        $data = $request->validate(['decision' => ['required', Rule::in(['accepted', 'declined'])]]);

        if ($data['decision'] === 'declined') {
            $recommendations = $attorneyMatcher->match($inquiry->only([
                'practice_area_id', 'client_location', 'legal_profile', 'message', 'client_requirements', 'budget_range',
            ]), 10)->reject(fn ($attorney) => $attorney->id === $request->user()->attorney_id);
            $next = $recommendations->first();
            $inquiry->update(['attorney_id' => $next?->id, 'status' => $next ? 'referred' : 'declined']);
            InquiryMessage::create([
                'inquiry_id' => $inquiry->id,
                'sender_role' => 'assistant',
                'sender_name' => 'GPMO Matching Assistant',
                'body' => $next
                    ? 'The first attorney was unavailable. Your request was automatically sent to the next best match, ' . $next->name . '.'
                    : 'The attorney could not accept and no other connected attorney matches this request. The firm will follow up with you.',
            ]);

            return back()->with('attorney_status', $next ? 'Request declined and automatically routed to ' . $next->name . '.' : 'Request declined. No other connected match is available; the firm will follow up.');
        }

        $case = \Illuminate\Support\Facades\DB::transaction(function () use ($inquiry) {
            $attorney = $inquiry->attorney;
            abort_unless($attorney && $attorney->is_available, 422, 'This attorney is no longer available.');

            do {
                $reference = 'SAFA-' . now()->format('Y') . '-' . \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(6));
            } while (CaseRecord::where('reference_no', $reference)->exists());

            $acceptedAt = now();
            $case = CaseRecord::create([
                'inquiry_id' => $inquiry->id,
                'reference_no' => $reference,
                'client_email' => $inquiry->email,
                'title' => \Illuminate\Support\Str::limit($inquiry->message, 120, ''),
                'attorney_id' => $attorney->id,
                'practice_area_id' => $inquiry->practice_area_id,
                'status' => 'active',
                'submitted_at' => $inquiry->created_at,
                'accepted_at' => $acceptedAt,
                'timeline' => [
                    ['label' => 'Client intake received', 'date' => $inquiry->created_at->toDateString(), 'state' => 'done'],
                    ['label' => 'Attorney accepted consultation', 'date' => $acceptedAt->toDateString(), 'state' => 'done'],
                    ['label' => 'Consultation confirmed', 'date' => $acceptedAt->toDateString(), 'state' => 'current'],
                ],
            ]);
            $inquiry->update(['status' => 'accepted']);

            return $case;
        });

        $notification = new ConsultationConfirmedNotification($case, $inquiry);
        $recipients = collect([
            User::query()->whereRaw('LOWER(email) = ?', [mb_strtolower($inquiry->email)])->first(),
            $inquiry->attorney?->account,
        ])->filter()->unique('id');

        $smtpCredentialsMissing = config('mail.default') === 'smtp'
            && (! filled(config('mail.mailers.smtp.username')) || ! filled(config('mail.mailers.smtp.password')));
        if ($smtpCredentialsMissing || ! filled(config('mail.from.address'))) {
            Log::warning('Consultation was confirmed, but email notification was skipped because mail settings are incomplete.', [
                'inquiry_id' => $inquiry->id,
                'case_id' => $case->id,
            ]);
        } else {
            foreach ($recipients as $recipient) {
                try {
                    $recipient->notify($notification);
                } catch (\Throwable $exception) {
                    Log::warning('Consultation was confirmed, but its email notification could not be sent.', [
                        'inquiry_id' => $inquiry->id,
                        'case_id' => $case->id,
                        'recipient_id' => $recipient->id,
                        'error' => $exception->getMessage(),
                    ]);
                }
            }
        }

        return back()->with('attorney_status', 'Consultation accepted. Case ' . $case->reference_no . ' is now confirmed.');
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
