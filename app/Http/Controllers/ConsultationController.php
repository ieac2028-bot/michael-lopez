<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use App\Models\PracticeArea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Handles consultation request form submissions — distinct from
 * ContactController's general inquiries. A client lands here when they
 * already know they want to sit down with an attorney, so this form asks
 * for more commitment-level detail: which practice area, a phone number,
 * and a preferred date/time, demonstrating the request flow in this prototype.
 */
class ConsultationController extends Controller
{
    private const TIME_SLOTS = [
        'Morning (9:00 AM – 12:00 PM)',
        'Afternoon (1:00 PM – 5:00 PM)',
    ];

    public function show()
    {
        $practiceAreas = PracticeArea::orderBy('sort_order')->get();
        $timeSlots = self::TIME_SLOTS;

        return view('pages.consultation', compact('practiceAreas', 'timeSlots'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['required', 'string', 'max:30'],
            'practice_area_id' => ['required', 'exists:practice_areas,id'],
            'preferred_date' => ['required', 'date', 'after_or_equal:today'],
            'preferred_time' => ['required', 'in:' . implode(',', self::TIME_SLOTS)],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        Inquiry::create($data + [
            'status' => 'new',
            'type' => Inquiry::TYPE_CONSULTATION,
        ]);

        return redirect()
            ->route('consultation')
            ->with('status', 'Sample consultation request saved in the project database. This prototype does not contact or schedule an attorney.');
    }
}
