<?php

namespace App\Http\Controllers;

use App\Models\Attorney;
use App\Models\CaseRecord;
use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function index()
    {
        $inquiries = Inquiry::with(['practiceArea', 'attorney', 'caseRecord'])->latest()->get();
        $cases = CaseRecord::with(['attorney', 'practiceArea'])->latest()->get();
        $attorneys = Attorney::with(['practiceArea', 'account'])
            ->orderByDesc('sample_rating')
            ->orderByDesc('years_experience')
            ->get();

        return view('admin.dashboard', [
            'inquiries' => $inquiries,
            'cases' => $cases,
            'attorneys' => $attorneys,
            'pendingInquiryCount' => $inquiries->whereIn('status', ['new', 'reviewing', 'clarifying', 'referred'])->count(),
            'openCaseCount' => $cases->whereIn('status', ['active', 'on_hold'])->count(),
            'availableAttorneyCount' => $attorneys->where('is_available', true)->count(),
        ]);
    }

    public function updateInquiry(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['new', 'reviewing', 'clarifying', 'referred', 'accepted', 'declined', 'closed'])],
            'attorney_id' => ['nullable', 'integer', 'exists:attorneys,id'],
        ]);

        if ($data['attorney_id'] && $inquiry->practice_area_id
            && Attorney::whereKey($data['attorney_id'])->value('practice_area_id') !== $inquiry->practice_area_id) {
            return back()->withErrors(['attorney_id' => 'Choose an attorney in the selected practice area.']);
        }

        $inquiry->update($data);

        return back()->with('admin_status', 'Intake and referral assignment updated.');
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
        ]);

        return back()->with('admin_status', 'Attorney availability and service coverage saved.');
    }
}
