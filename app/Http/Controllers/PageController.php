<?php

namespace App\Http\Controllers;

use App\Models\Attorney;
use App\Models\PracticeArea;

class PageController extends Controller
{
    public function home()
    {
        $practiceAreas = PracticeArea::orderBy('sort_order')->take(4)->get();

        return view('pages.home', compact('practiceAreas'));
    }

    public function attorneys()
    {
        $rankOrder = ['Managing Partner', 'Senior Partner', 'Senior Associate', 'Associate', 'Legal Staff'];

        $attorneys = Attorney::with('practiceArea')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('rank')
            ->sortBy(fn ($group, $rank) => array_search($rank, $rankOrder) === false
                ? 99
                : array_search($rank, $rankOrder));
        $attorneyCount = $attorneys->sum(fn ($group) => $group->count());

        return view('pages.attorneys', compact('attorneys', 'attorneyCount'));
    }

    public function practiceAreas()
    {
        $practiceAreas = PracticeArea::with('attorneys')
            ->get()
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        return view('pages.practice-areas', compact('practiceAreas'));
    }

    public function firm()
    {
        return view('pages.firm');
    }

    public function legalInsights()
    {
        return view('pages.legal-insights');
    }

    public function aboutUs()
    {
        return view('pages.about-us');
    }
}
