<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;

class ClientPortalController extends Controller
{
    public function show()
    {
        $inquiry = Inquiry::query()
            ->whereRaw('LOWER(email) = ?', [mb_strtolower(auth()->user()->email)])
            ->where('type', Inquiry::TYPE_CONSULTATION)
            ->latest()
            ->first();

        if ($inquiry) {
            session()->put('consultation_inquiry_id', $inquiry->id);
        }

        return redirect()->route('consultation');
    }
}
