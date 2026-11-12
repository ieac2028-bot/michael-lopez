<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use App\Models\InquiryMessage;
use App\Models\User;
use App\Services\AttorneyMatchingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Starts the client-facing conversation. Staff collect the structured intake
 * details and trigger attorney matching after the chat.
 */
class ConsultationController extends Controller
{
    public function show(AttorneyMatchingService $attorneyMatcher)
    {
        $recommendedAttorneys = collect();
        $inquiry = Inquiry::query()->with('messages')
            ->whereKey(session('consultation_inquiry_id'))
            ->whereRaw('LOWER(email) = ?', [mb_strtolower(auth()->user()->email)])
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

        return view('pages.consultation', compact('recommendedAttorneys', 'inquiry', 'adminOnline'));
    }

    public function adminStatus()
    {
        return response()->json(['online' => $this->adminIsOnline()]);
    }

    public function open(Request $request, Inquiry $inquiry): RedirectResponse
    {
        abort_unless(mb_strtolower($inquiry->email) === mb_strtolower($request->user()->email), 404);

        $request->session()->put('consultation_inquiry_id', $inquiry->id);

        return redirect()->route('consultation');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);
        $inquiry = Inquiry::create([
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
        abort_unless(mb_strtolower($inquiry->email) === mb_strtolower($request->user()->email), 404);
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
        abort_unless(mb_strtolower($inquiry->email) === mb_strtolower($request->user()->email), 404);

        return response()->json([
            'status' => $inquiry->status,
            'attorney_id' => $inquiry->attorney_id,
            'admin_online' => $this->adminIsOnline(),
            'messages' => $inquiry->messages()->oldest()->get(['id', 'sender_role', 'sender_name', 'body', 'created_at']),
        ]);
    }

    private function adminIsOnline(): bool
    {
        return User::query()
            ->where(fn ($query) => $query->where('role', 'admin')->orWhere('is_admin', true))
            ->pluck('id')
            ->contains(fn ($id) => Cache::has('gpmo-admin-presence:'.$id));
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
