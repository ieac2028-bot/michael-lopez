<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use App\Services\ConsultationPaymentService;

class ClientPortalController extends Controller
{
    public function show(ConsultationPaymentService $payments)
    {
        $payments->expireDueProposals();
        $client = auth()->user();
        $inquiries = Inquiry::query()
            ->where('type', Inquiry::TYPE_CONSULTATION)
            ->where(function ($query) use ($client) {
                $query->where('client_id', $client->id)
                    ->orWhere(function ($legacy) use ($client) {
                        $legacy->whereNull('client_id')
                            ->whereRaw('LOWER(email) = ?', [mb_strtolower($client->email)]);
                    });
            })
            ->with(['practiceArea', 'attorney', 'caseRecord'])
            ->latest()
            ->get();

        return view('pages.client-portal', compact('inquiries'));
    }
}
