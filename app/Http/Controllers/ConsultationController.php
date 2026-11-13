<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use App\Models\InquiryMessage;
use App\Models\PracticeArea;
use App\Models\User;
use App\Services\AttorneyMatchingService;
use App\Services\ConsultationPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Starts the client-facing conversation. Staff collect the structured intake
 * details and trigger attorney matching after the chat.
 */
class ConsultationController extends Controller
{
    public function show(AttorneyMatchingService $attorneyMatcher, ConsultationPaymentService $payments)
    {
        $payments->expireDueProposals();
        $recommendedAttorneys = collect();
        $inquiry = Inquiry::query()->with(['messages', 'caseRecord', 'attorney.practiceArea'])
            ->whereKey(session('consultation_inquiry_id'))
            ->where(function ($query) {
                $query->where('client_id', auth()->id())
                    ->orWhere(function ($legacy) {
                        $legacy->whereNull('client_id')
                            ->whereRaw('LOWER(email) = ?', [mb_strtolower(auth()->user()->email)]);
                    });
            })
            ->first();
        if ($inquiry && $inquiry->recommended_attorney_ids) {
            $ranked = $attorneyMatcher->match($inquiry->only([
                'practice_area_id', 'client_location', 'legal_profile', 'message',
                'client_requirements', 'budget_range',
            ]), 10)->keyBy('id');
            $recommendedAttorneys = collect($inquiry->recommended_attorney_ids)
                ->map(fn ($id) => $ranked->get($id))->filter();
            $recommendedAttorneys = $recommendedAttorneys
                ->filter(fn ($attorney) => $attorney->id === $inquiry->attorney_id)
                ->values();
        }

        $adminOnline = $this->adminIsOnline();
        $attorneyPracticeAreas = collect();
        if ($inquiry?->payment_status === 'verified' && $inquiry->attorney) {
            $areaIds = collect([$inquiry->attorney->practice_area_id])
                ->merge($inquiry->attorney->additional_practice_area_ids ?? [])
                ->filter()->unique()->all();
            $attorneyPracticeAreas = PracticeArea::query()->whereIn('id', $areaIds)->orderBy('name')->pluck('name');
        }

        return view('pages.consultation', compact('recommendedAttorneys', 'inquiry', 'adminOnline', 'attorneyPracticeAreas'));
    }

    public function adminStatus()
    {
        return response()->json(['online' => $this->adminIsOnline()]);
    }

    public function open(Request $request, Inquiry $inquiry): RedirectResponse
    {
        abort_unless($this->ownsInquiry($inquiry, $request->user()), 404);

        $request->session()->put('consultation_inquiry_id', $inquiry->id);

        return redirect()->route('consultation');
    }

    public function startNew(Request $request): RedirectResponse
    {
        $request->session()->forget('consultation_inquiry_id');

        return redirect()->route('consultation')->with('status', 'Start a new matching request below. Your previous consultation history remains in your Client Portal.');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);
        $inquiry = Inquiry::create([
            'client_id' => $request->user()->id,
            'full_name' => $request->user()->name,
            'email' => $request->user()->email,
            'message' => $data['body'],
            'status' => 'new',
            'type' => Inquiry::TYPE_CONSULTATION,
        ]);
        InquiryMessage::create([
            'inquiry_id' => $inquiry->id,
            'sender_role' => 'client',
            'sender_name' => $request->user()->name,
            'body' => $data['body'],
        ]);
        $this->appendOfflineAssistantReply($inquiry, $data['body']);
        $request->session()->put('consultation_inquiry_id', $inquiry->id);

        return redirect()
            ->route('consultation')
            ->with('status', 'Your conversation with the firm has started. Staff will ask follow-up questions before matching attorneys.');
    }

    public function sendMessage(Request $request, Inquiry $inquiry): RedirectResponse
    {
        abort_unless($this->ownsInquiry($inquiry, $request->user()), 404);
        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);
        InquiryMessage::create([
            'inquiry_id' => $inquiry->id,
            'sender_role' => 'client',
            'sender_name' => $request->user()->name,
            'body' => $data['body'],
        ]);
        $this->appendOfflineAssistantReply($inquiry, $data['body']);

        $request->session()->put('consultation_inquiry_id', $inquiry->id);

        return redirect()->route('consultation');
    }

    public function pollMessages(Request $request, Inquiry $inquiry)
    {
        abort_unless($this->ownsInquiry($inquiry, $request->user()), 404);
        app(ConsultationPaymentService::class)->expireDueProposals();
        $inquiry->refresh();

        return response()->json([
            'status' => $inquiry->status,
            'payment_status' => $inquiry->payment_status,
            'refund_requested_at' => $inquiry->refund_requested_at?->toISOString(),
            'attorney_id' => $inquiry->attorney_id,
            'admin_online' => $this->adminIsOnline(),
            'messages' => $inquiry->messages()->oldest()->get(['id', 'sender_role', 'sender_name', 'body', 'created_at']),
        ]);
    }

    public function markPaymentSent(Request $request, Inquiry $inquiry): RedirectResponse
    {
        abort_unless($this->ownsInquiry($inquiry, $request->user()), 404);
        app(ConsultationPaymentService::class)->expireDueProposals();
        $inquiry->refresh();
        if ($inquiry->type !== Inquiry::TYPE_CONSULTATION || $inquiry->payment_status !== 'awaiting_client') {
            return back()->with('status', $inquiry->payment_status === 'expired' ? 'This payment request expired. The consultation was not confirmed; message the office to continue.' : 'This payment request is no longer awaiting your response.');
        }
        if ($inquiry->payment_expires_at && $inquiry->payment_expires_at->isPast()) {
            app(ConsultationPaymentService::class)->expireDueProposals();
            return back()->with('status', 'This payment request expired. The consultation was not confirmed; message the office to continue.');
        }
        $data = $request->validate([
            'payment_reference' => ['nullable', 'string', 'max:120'],
            'acknowledge_terms' => ['required', 'accepted'],
        ]);
        $reference = trim($data['payment_reference'] ?? '');

        $inquiry->update([
            'payment_status' => 'pending',
            'payment_requested_at' => now(),
            'payment_terms_accepted_at' => now(),
            'payment_reference' => $reference !== '' ? $reference : null,
        ]);
        InquiryMessage::create([
            'inquiry_id' => $inquiry->id,
            'sender_role' => 'client',
            'sender_name' => $request->user()->name,
            'body' => 'I have sent the consultation payment. Please verify it with the office account.' . ($reference !== '' ? ' Payment reference: ' . $reference . '.' : ''),
        ]);

        return back()->with('status', 'Your payment notice was sent. The office will check the account and confirm the consultation after the payment is verified.');
    }

    public function cancelPayment(Request $request, Inquiry $inquiry): RedirectResponse
    {
        abort_unless($this->ownsInquiry($inquiry, $request->user()), 404);
        app(ConsultationPaymentService::class)->expireDueProposals();
        $inquiry->refresh();
        if ($inquiry->type !== Inquiry::TYPE_CONSULTATION || $inquiry->payment_status !== 'awaiting_client') {
            return back()->with('status', $inquiry->payment_status === 'expired' ? 'This payment request already expired.' : 'This request can only be canceled before you report payment.');
        }
        $inquiry->update(['payment_status' => 'canceled', 'payment_canceled_at' => now()]);
        InquiryMessage::create([
            'inquiry_id' => $inquiry->id,
            'sender_role' => 'client',
            'sender_name' => $request->user()->name,
            'body' => 'I am canceling this consultation payment request before paying. Please keep the conversation so I can contact the office if I want to continue later.',
        ]);

        return back()->with('status', 'Payment request canceled. The consultation is not confirmed; your conversation remains available.');
    }

    public function requestRefundReview(Request $request, Inquiry $inquiry): RedirectResponse
    {
        abort_unless($this->ownsInquiry($inquiry, $request->user()), 404);
        abort_unless($inquiry->type === Inquiry::TYPE_CONSULTATION && in_array($inquiry->payment_status, ['pending', 'verifying', 'verified'], true), 409, 'There is no reported payment to review.');
        $inquiry->update(['refund_requested_at' => now()]);
        InquiryMessage::create([
            'inquiry_id' => $inquiry->id,
            'sender_role' => 'client',
            'sender_name' => $request->user()->name,
            'body' => 'I would like the firm to review canceling this consultation and any applicable refund. I understand the office must review the request; the payment or confirmed consultation is not canceled automatically.',
        ]);

        return back()->with('status', 'Your cancellation and refund review request was sent to the office. The consultation remains unchanged until the firm reviews it.');
    }

    private function adminIsOnline(): bool
    {
        return User::query()
            ->administrators()
            ->active()
            ->pluck('id')
            ->contains(fn ($id) => Cache::has('gpmo-admin-presence:'.$id));
    }

    private function ownsInquiry(Inquiry $inquiry, User $client): bool
    {
        return $inquiry->client_id
            ? (int) $inquiry->client_id === (int) $client->id
            : mb_strtolower($inquiry->email) === mb_strtolower($client->email);
    }

    private function appendOfflineAssistantReply(Inquiry $inquiry, string $clientMessage): void
    {
        if ($this->adminIsOnline()) {
            return;
        }

        InquiryMessage::create([
            'inquiry_id' => $inquiry->id,
            'sender_role' => 'assistant',
            'sender_name' => 'GPMO Assistant',
            'body' => app(ChatbotController::class)->consultationFallbackReply($clientMessage),
        ]);
    }
}
