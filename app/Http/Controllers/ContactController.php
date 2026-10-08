<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use App\Models\PracticeArea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Handles general questions only — "I have a quick question" territory.
 * For an actual consultation booking request, see ConsultationController.
 * The two form types are stored separately to demonstrate the different
 * inquiry flows in the student project prototype.
 */
class ContactController extends Controller
{
    public function show()
    {
        $practiceAreas = PracticeArea::orderBy('sort_order')->get();

        return view('pages.contact', compact('practiceAreas'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:75'],
            'last_name' => ['required', 'string', 'max:75'],
            'email' => ['required', 'email', 'max:150'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $fullName = trim($data['first_name'] . ' ' . $data['last_name']);
        unset($data['first_name'], $data['last_name']);

        Inquiry::create($data + [
            'full_name' => $fullName,
            'practice_area_id' => null,
            'status' => 'new',
            'type' => Inquiry::TYPE_GENERAL,
        ]);

        return redirect()
            ->route('contact')
            ->with('status', 'Your message was saved by this project. The form is not yet connected to the firm’s email, so for a direct response please call or email SAFALAW below.');
    }
}
