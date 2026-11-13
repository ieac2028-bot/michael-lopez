<?php

namespace App\Http\Controllers;

use App\Models\CaseRecord;
use App\Models\Inquiry;
use App\Services\AttorneyMatchingService;
use App\Models\InquiryMessage;
use App\Models\PracticeArea;
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
        $attorneyProfile = $request->user()->attorneyProfile;
        abort_unless($attorneyProfile, 403, 'This account is not linked to an attorney profile.');

        $inquiries = Inquiry::query()
            ->with(['practiceArea', 'messages'])
            ->where('attorney_id', $request->user()->attorney_id)
            ->where('status', 'referred')
            ->whereNull('payment_status')
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

        $practiceAreas = PracticeArea::query()->orderBy('sort_order')->orderBy('name')->get();
        $languageOptions = ['English', 'Filipino/Tagalog', 'Cebuano', 'Ilocano', 'Hiligaynon/Ilonggo', 'Bikol', 'Waray', 'Kapampangan', 'Pangasinan', 'Maranao', 'Tausug'];

        return view('attorney.dashboard', compact('attorneyProfile', 'cases', 'inquiries', 'practiceAreas', 'languageOptions'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $languageOptions = ['English', 'Filipino/Tagalog', 'Cebuano', 'Ilocano', 'Hiligaynon/Ilonggo', 'Bikol', 'Waray', 'Kapampangan', 'Pangasinan', 'Maranao', 'Tausug'];
        $data = $request->validate([
            'primary_practice_area_id' => ['required', 'integer', 'exists:practice_areas,id'],
            'budget_min' => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'budget_max' => ['nullable', 'integer', 'min:0', 'max:100000000', 'gte:budget_min'],
            'contact_email' => ['nullable', 'email', 'max:150'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'specialization_note' => ['nullable', 'string', 'max:1000'],
            'years_experience' => ['nullable', 'integer', 'min:0', 'max:70'],
            'coverage_areas' => ['nullable', 'string', 'max:500'],
            'spoken_languages' => ['required', 'array', 'min:1'],
            'spoken_languages.*' => [Rule::in($languageOptions)],
            'other_languages' => ['nullable', 'string', 'max:300'],
        ]);

        $attorney = $request->user()->attorneyProfile;
        abort_unless($attorney, 403);

        $primaryAreaId = (int) $data['primary_practice_area_id'];
        $coverage = collect(explode(',', $data['coverage_areas'] ?? ''))
            ->map(fn ($area) => trim($area))
            ->filter()->unique(fn ($area) => mb_strtolower($area))->values()->all();
        $languages = collect($data['spoken_languages'])
            ->merge(explode(',', $data['other_languages'] ?? ''))
            ->map(fn ($language) => trim($language))
            ->filter()->unique(fn ($language) => mb_strtolower($language))->values()->all();

        $attorney->update([
            'practice_area_id' => $primaryAreaId,
            'budget_min' => $data['budget_min'] ?? null,
            'budget_max' => $data['budget_max'] ?? null,
            'contact_email' => isset($data['contact_email']) ? mb_strtolower($data['contact_email']) : null,
            'contact_phone' => $data['contact_phone'] ?? null,
            'specialization_note' => $data['specialization_note'] ?? null,
            'years_experience' => $data['years_experience'] ?? null,
            'coverage_areas' => $coverage,
            'spoken_languages' => $languages,
        ]);

        return back()->with('attorney_status', 'Your professional profile was saved. Public contact details appear to clients only after a consultation is confirmed.');
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

        $data = $request->validate([
            'decision' => ['required', Rule::in(['accepted', 'declined'])],
            'consultation_fee' => ['required_if:decision,accepted', 'nullable', 'numeric', 'gt:0', 'max:9999999999'],
        ]);

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

        abort_unless($inquiry->attorney?->is_available, 422, 'This attorney is no longer available.');
        $inquiry->update([
            'consultation_fee' => $data['consultation_fee'],
            'proposed_consultation_fee' => $data['consultation_fee'],
            'payment_status' => 'fee_proposed',
            'fee_proposed_by' => $request->user()->id,
            'fee_proposed_at' => now(),
        ]);
        InquiryMessage::create([
            'inquiry_id' => $inquiry->id,
            'sender_role' => 'assistant',
            'sender_name' => 'GPMO Payment Desk',
            'body' => $request->user()->attorneyProfile->name . ' accepted your request and proposed a consultation fee of ₱' . number_format((float) $data['consultation_fee'], 2) . '. Firm staff will confirm the payment details in this chat before you are asked to pay.',
        ]);

        return back()->with('attorney_status', 'Consultation accepted. The proposed fee is waiting for firm staff to confirm payment details.');
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
