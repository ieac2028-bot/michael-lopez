<?php

namespace App\Http\Controllers;

use App\Models\Attorney;
use App\Models\CaseRecord;
use App\Models\Inquiry;
use App\Models\InquiryMessage;
use App\Models\PracticeArea;
use App\Services\AttorneyMatchingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function index()
    {
        Cache::put('gpmo-admin-presence:'.auth()->id(), true, now()->addSeconds(45));
        $inquiries = Inquiry::with(['practiceArea', 'attorney', 'caseRecord', 'messages'])->latest()->get();
        $cases = CaseRecord::with(['attorney', 'practiceArea'])->latest()->get();
        $attorneys = Attorney::with(['practiceArea', 'account'])
            ->orderByDesc('years_experience')
            ->get();
        $practiceAreas = PracticeArea::orderBy('sort_order')->get();

        return view('admin.dashboard', [
            'inquiries' => $inquiries,
            'cases' => $cases,
            'attorneys' => $attorneys,
            'practiceAreas' => $practiceAreas,
            'pendingInquiryCount' => $inquiries->whereIn('status', ['new', 'reviewing', 'clarifying', 'referred'])->count(),
            'openCaseCount' => $cases->whereIn('status', ['active', 'on_hold'])->count(),
            'availableAttorneyCount' => $attorneys->where('is_available', true)->count(),
        ]);
    }

    public function pollInquiryInbox()
    {
        Cache::put('gpmo-admin-presence:'.auth()->id(), true, now()->addSeconds(45));
        return response()->json(['latest_id' => Inquiry::max('id') ?? 0]);
    }

    public function updateInquiry(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in($inquiry->type === Inquiry::TYPE_CONSULTATION
                ? array_unique(['new', 'reviewing', 'clarifying', 'closed', $inquiry->status])
                : ['new', 'reviewing', 'clarifying', 'referred', 'accepted', 'declined', 'closed'])],
            'attorney_id' => ['nullable', 'integer', 'exists:attorneys,id'],
        ]);

        $submittedAttorneyId = filled($data['attorney_id'] ?? null) ? (int) $data['attorney_id'] : null;
        if ($inquiry->type === Inquiry::TYPE_CONSULTATION && $submittedAttorneyId !== $inquiry->attorney_id) {
            return back()->withErrors(['attorney_id' => 'Consultation referrals are assigned automatically from the best available match.']);
        }

        if ($data['attorney_id'] && $inquiry->practice_area_id
            && Attorney::whereKey($data['attorney_id'])->value('practice_area_id') !== $inquiry->practice_area_id) {
            return back()->withErrors(['attorney_id' => 'Choose an attorney in the selected practice area.']);
        }

        $inquiry->update($data);

        return back()->with('admin_status', 'Intake and referral assignment updated.');
    }

    public function sendInquiryMessage(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);
        InquiryMessage::create([
            'inquiry_id' => $inquiry->id,
            'sender_role' => 'admin',
            'sender_name' => $request->user()->name,
            'body' => $data['body'],
        ]);
        if ($inquiry->type === Inquiry::TYPE_CONSULTATION && $inquiry->status === 'new') {
            $inquiry->update(['status' => 'clarifying']);
        }

        return back()->with('admin_status', 'Message sent to the client.');
    }

    public function pollInquiryMessages(Inquiry $inquiry)
    {
        return response()->json([
            'status' => $inquiry->status,
            'messages' => $inquiry->messages()->oldest()->get(['id', 'sender_role', 'sender_name', 'body', 'created_at']),
        ]);
    }

    public function updateIntake(Request $request, Inquiry $inquiry, AttorneyMatchingService $attorneyMatcher): RedirectResponse
    {
        abort_unless($inquiry->type === Inquiry::TYPE_CONSULTATION, 404);
        $validator = Validator::make($request->all(), [
            'phone' => ['nullable', 'string', 'max:30'],
            'client_type' => ['nullable', Rule::in(['individual', 'business', 'organization'])],
            'client_location' => ['nullable', 'string', 'max:120'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'practice_area_id' => ['nullable', 'exists:practice_areas,id'],
            'legal_profile' => ['nullable', 'string', 'max:2000'],
            'client_requirements' => ['nullable', 'string', 'max:2000'],
            'budget_range' => ['nullable', Rule::in(['0-25000', '25000-75000', '75000-150000', '150000-500000', '500000-999999999'])],
            'consultation_method' => ['nullable', Rule::in(['in_person', 'video', 'phone'])],
            'preferred_date' => ['nullable', 'date', 'after_or_equal:today'],
            'preferred_time' => ['nullable', Rule::in(self::consultationTimeSlots())],
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('expanded_inquiry_id', $inquiry->id);
        }
        $data = $validator->validated();

        $inquiry->update($data + ['status' => 'clarifying']);
        $inquiry->refresh();

        if (! $inquiry->practice_area_id) {
            $description = implode(' ', array_filter([
                $inquiry->message,
                $inquiry->legal_profile,
                $inquiry->client_requirements,
            ]));
            $detectedArea = $attorneyMatcher->identifyPracticeArea($description);
            if ($detectedArea) {
                $inquiry->update(['practice_area_id' => $detectedArea->id]);
                $inquiry->refresh();
            }
        }

        if (! $inquiry->practice_area_id) {
            $inquiry->update(['recommended_attorney_ids' => [], 'attorney_id' => null, 'status' => 'clarifying']);

            return back()->with('admin_status', 'Intake saved. The issue is not clear enough for automatic category detection; choose a legal category or clarify the client’s concern.')
                ->with('expanded_inquiry_id', $inquiry->id);
        }

        $recommendations = $attorneyMatcher->match($inquiry->only([
            'practice_area_id', 'client_location', 'legal_profile', 'message',
            'client_requirements', 'budget_range',
        ]), 10);
        $matchedIds = $recommendations->pluck('id')->all();
        $selected = $recommendations->first();
        $inquiry->update([
            'recommended_attorney_ids' => $matchedIds,
            'attorney_id' => $selected?->id,
            'status' => $selected ? 'referred' : 'reviewing',
        ]);

        $category = $inquiry->practiceArea?->name ?? 'the selected legal category';
        $message = $selected
            ? 'We identified ' . $category . ' and automatically sent your consultation request to ' . $selected->name . ', the best available match. The attorney will review the request and respond.'
            : 'We identified ' . $category . ', but no available attorney account currently matches the intake details. The firm will follow up.';
        InquiryMessage::create([
            'inquiry_id' => $inquiry->id,
            'sender_role' => 'assistant',
            'sender_name' => 'GPMO Matching Assistant',
            'body' => $message,
        ]);

        return back()->with('admin_status', $selected
            ? 'Intake saved. The best match, ' . $selected->name . ', received the consultation request automatically.'
            : 'Intake saved. No connected attorney account matched this category and the intake details.')
            ->with('expanded_inquiry_id', $inquiry->id);
    }

    public function prepareMatches(Request $request, Inquiry $inquiry, AttorneyMatchingService $attorneyMatcher): RedirectResponse
    {
        abort_unless($inquiry->type === Inquiry::TYPE_CONSULTATION, 404);
        $validator = Validator::make($request->all(), [
            'phone' => ['required', 'string', 'max:30'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'client_type' => ['required', Rule::in(['individual', 'business', 'organization'])],
            'client_location' => ['required', 'string', 'max:120'],
            'practice_area_id' => ['required', 'exists:practice_areas,id'],
            'legal_profile' => ['required', 'string', 'max:2000'],
            'client_requirements' => ['required', 'string', 'max:2000'],
            'budget_range' => ['required', Rule::in(['0-25000', '25000-75000', '75000-150000', '150000-500000', '500000-999999999'])],
            'consultation_method' => ['required', Rule::in(['in_person', 'video', 'phone'])],
            'preferred_date' => ['required', 'date', 'after_or_equal:today'],
            'preferred_time' => ['required', Rule::in(self::consultationTimeSlots())],
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('expanded_inquiry_id', $inquiry->id);
        }
        $data = $validator->validated();

        $recommendations = $attorneyMatcher->match($data + ['message' => $inquiry->message]);
        $selected = $recommendations->first();
        $inquiry->update($data + [
            'recommended_attorney_ids' => $recommendations->pluck('id')->all(),
            'attorney_id' => $selected?->id,
            'status' => $selected ? 'referred' : 'reviewing',
        ]);
        InquiryMessage::create([
            'inquiry_id' => $inquiry->id,
            'sender_role' => 'admin',
            'sender_name' => 'GPMO Matching Team',
            'body' => 'We identified ' . PracticeArea::find($data['practice_area_id'])->name
                . ' as the legal category and prepared ' . $recommendations->count()
                . ($selected ? '. The best match, ' . $selected->name . ', received the request automatically.' : ', but no connected attorney account is available.'),
        ]);

        return back()->with('admin_status', $selected ? 'Legal category identified and best match request sent to ' . $selected->name . '.' : 'Legal category identified, but no connected attorney match is available.')
            ->with('expanded_inquiry_id', $inquiry->id);
    }

    private static function consultationTimeSlots(): array
    {
        return ['Morning (9:00 AM – 12:00 PM)', 'Afternoon (1:00 PM – 5:00 PM)'];
    }

    public function acceptInquiry(Inquiry $inquiry): RedirectResponse
    {
        if ($inquiry->type !== Inquiry::TYPE_CONSULTATION) {
            return back()->withErrors(['inquiry' => 'Only consultation intakes can be opened as a case.']);
        }

        $attorney = $inquiry->attorney;
        if (! $attorney || ! $attorney->is_available
            || ($inquiry->practice_area_id && $attorney->practice_area_id !== $inquiry->practice_area_id)) {
            return back()->withErrors(['inquiry' => 'Assign an available attorney for this practice area before accepting the referral.']);
        }

        $existing = CaseRecord::where('inquiry_id', $inquiry->id)->first();
        if ($existing) {
            return back()->with('admin_status', 'This intake already has case reference ' . $existing->reference_no . '.');
        }

        $case = DB::transaction(function () use ($inquiry, $attorney) {
            do {
                $reference = 'SAFA-' . now()->format('Y') . '-' . Str::upper(Str::random(6));
            } while (CaseRecord::where('reference_no', $reference)->exists());

            $acceptedAt = now();
            $case = CaseRecord::create([
                'inquiry_id' => $inquiry->id,
                'reference_no' => $reference,
                'client_email' => $inquiry->email,
                'title' => Str::limit($inquiry->message, 120, ''),
                'attorney_id' => $attorney->id,
                'practice_area_id' => $inquiry->practice_area_id,
                'status' => 'active',
                'submitted_at' => $inquiry->created_at,
                'accepted_at' => $acceptedAt,
                'timeline' => [
                    ['label' => 'Intake submitted', 'date' => $inquiry->created_at->toDateString(), 'state' => 'done'],
                    ['label' => 'Referral accepted by ' . $attorney->name, 'date' => $acceptedAt->toDateString(), 'state' => 'done'],
                    ['label' => 'Matter in progress', 'date' => null, 'state' => 'current'],
                ],
            ]);

            $inquiry->update(['status' => 'accepted']);

            return $case;
        });

        return back()->with('admin_status', 'Case ' . $case->reference_no . ' opened and acceptance time recorded.');
    }

    public function updateCase(Request $request, CaseRecord $caseRecord): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['active', 'on_hold', 'resolved', 'closed'])],
            'outcome' => ['nullable', 'string', 'max:150'],
            'testimony' => ['nullable', 'string', 'max:5000'],
        ]);

        $timeline = $caseRecord->timeline ?? [];
        $timeline = array_map(function ($step) {
            if (($step['state'] ?? null) === 'current') $step['state'] = 'done';
            return $step;
        }, $timeline);
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
        } elseif (! empty($timeline)) {
            $lastIndex = array_key_last($timeline);
            $timeline[$lastIndex]['state'] = in_array($data['status'], ['resolved', 'closed'], true) ? 'done' : 'current';
        }

        $data['timeline'] = $timeline;
        $data['resolved_at'] = in_array($data['status'], ['resolved', 'closed'], true)
            ? ($caseRecord->resolved_at ?? now())
            : null;
        $caseRecord->update($data);

        return back()->with('admin_status', 'Case status, timeline, outcome, and private testimony notes saved.');
    }

    public function updateAttorney(Request $request, Attorney $attorney): RedirectResponse
    {
        $data = $request->validate([
            'is_available' => ['required', 'boolean'],
            'coverage_areas' => ['nullable', 'string', 'max:500'],
            'budget_min' => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'budget_max' => ['nullable', 'integer', 'min:0', 'max:100000000', 'gte:budget_min'],
        ]);

        $coverage = collect(explode(',', $data['coverage_areas'] ?? ''))
            ->map(fn ($area) => trim($area))
            ->filter()
            ->unique(fn ($area) => mb_strtolower($area))
            ->values()
            ->all();

        $attorney->update([
            'is_available' => (bool) $data['is_available'],
            'coverage_areas' => $coverage,
            'budget_min' => $data['budget_min'] ?? null,
            'budget_max' => $data['budget_max'] ?? null,
        ]);

        return back()->with('admin_status', 'Attorney availability and service coverage saved.');
    }
}
