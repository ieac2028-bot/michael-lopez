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

        return view('pages.attorneys', compact('attorneys'));
    }

    public function practiceAreas()
    {
        $practiceAreas = PracticeArea::with('attorneys')->orderBy('sort_order')->get();

        return view('pages.practice-areas', compact('practiceAreas'));
    }

    public function firm()
    {
        return view('pages.firm');
    }

    public function aboutUs()
    {
        return view('pages.about-us');
    }
}
