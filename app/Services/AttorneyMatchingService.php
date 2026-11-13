<?php

namespace App\Services;

use App\Models\Attorney;
use App\Models\PracticeArea;
use Illuminate\Support\Collection;

class AttorneyMatchingService
{
    private const CATEGORY_KEYWORDS = [
        'Commercial Law' => ['business', 'company', 'corporate', 'commercial', 'shareholder', 'incorporation', 'joint venture', 'merger', 'contract drafting'],
        'Family Relations and Estate Planning' => ['family law', 'child custody', 'custody', 'inheritance', 'estate planning', 'last will', 'adoption', 'succession'],
        'Immigration Law' => ['immigration', 'visa', 'work permit', 'residency', 'citizenship', 'deportation', 'alien registration'],
        'Intellectual Property' => ['intellectual property', 'trademark', 'copyright', 'patent', 'brand infringement', 'invention', 'licensing'],
        'Investments' => ['investment', 'foreign investor', 'securities', 'boi', 'peza', 'capital markets'],
        'Labor Law' => ['labor law', 'employment', 'employee', 'unpaid wages', 'salary', 'overtime', 'wrongful dismissal', 'workplace'],
        'Litigation' => ['litigation', 'lawsuit', 'court case', 'sued', 'arbitration', 'mediation', 'civil dispute', 'breach of contract'],
        'Maritime Law' => ['maritime', 'vessel', 'shipping', 'cargo claim', 'seafarer', 'bill of lading'],
        'Property' => ['property', 'real estate', 'land title', 'land ownership', 'lease', 'boundary dispute', 'conveyance'],
        'Special Projects' => ['special project', 'public private partnership', 'infrastructure project', 'ppp'],
        'Taxation' => ['taxation', 'tax', 'bir audit', 'vat', 'tax assessment', 'tax filing'],
    ];

    /** Suggest a practice area only when the client's wording has a clear keyword winner. */
    public function identifyPracticeArea(string $description): ?PracticeArea
    {
        $text = mb_strtolower(trim($description));
        if ($text === '') {
            return null;
        }

        $scores = [];
        foreach (self::CATEGORY_KEYWORDS as $area => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($text, $keyword)) {
                    $scores[$area] = ($scores[$area] ?? 0) + (mb_strlen($keyword) >= 9 ? 2 : 1);
                }
            }
        }

        if ($scores === []) {
            return null;
        }

        arsort($scores);
        $topScore = reset($scores);
        $winners = array_keys(array_filter($scores, fn (int $score) => $score === $topScore));
        if (count($winners) !== 1) {
            return null;
        }

        return PracticeArea::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($winners[0])])->first();
    }

    /**
     * Suggest available attorneys for an intake, ranked by the client's issue,
     * stated location, and relevant experience.
     *
     * Practice area remains a required eligibility match. Issue keywords and
     * coverage are used to rank attorneys within that practice area. Budget fit
     * is used when attorney fee ranges have been configured by staff.
     */
    public function match(array $intake, int $limit = 3): Collection
    {
        $location = mb_strtolower(trim($intake['client_location'] ?? ''));
        $issueTerms = $this->terms(implode(' ', array_filter([
            $intake['legal_profile'] ?? '',
            $intake['message'] ?? '',
            $intake['client_requirements'] ?? '',
        ])));
        $budgetRange = $intake['budget_range'] ?? null;

        $practiceAreaId = (int) ($intake['practice_area_id'] ?? 0);

        return Attorney::query()
            ->with('practiceArea')
            ->where('is_available', true)
            ->where('accepting_matches', true)
            ->whereHas('account')
            ->get()
            ->filter(fn (Attorney $attorney) => $practiceAreaId === (int) $attorney->practice_area_id
                || in_array($practiceAreaId, array_map('intval', $attorney->additional_practice_area_ids ?? []), true))
            ->map(function (Attorney $attorney) use ($location, $issueTerms, $budgetRange) {
                $specializationTerms = $this->terms($attorney->specialization_note ?? '');
                $relevantTerms = array_intersect($issueTerms, $specializationTerms);
                $coverageScore = $this->coverageScore($attorney, $location);
                $budgetScore = $this->budgetScore($attorney, $budgetRange);
                $score = min(100, 40 + min(count($relevantTerms) * 5, 25)
                    + $coverageScore + $budgetScore
                    + min((int) $attorney->years_experience, 10));
                $attorney->setAttribute('matching_score', $score);

                return [
                    'attorney' => $attorney,
                    'score' => $score,
                    'coverage_score' => $coverageScore,
                    'budget_score' => $budgetScore,
                ];
            })
            ->filter(fn (array $match) => $match['coverage_score'] > 0 && $match['budget_score'] >= 0)
            ->sort(function (array $a, array $b) {
                return ($b['score'] <=> $a['score'])
                    ?: ($b['attorney']->years_experience <=> $a['attorney']->years_experience)
                    ?: ($a['attorney']->sort_order <=> $b['attorney']->sort_order);
            })
            ->take(max(1, $limit))
            ->pluck('attorney')
            ->values();
    }

    private function budgetScore(Attorney $attorney, ?string $budgetRange): int
    {
        if (! $budgetRange || $attorney->budget_min === null || $attorney->budget_max === null) {
            return 0;
        }

        [$clientMin, $clientMax] = array_map('intval', explode('-', $budgetRange, 2));
        return $attorney->budget_min <= $clientMax && $attorney->budget_max >= $clientMin ? 15 : -1;
    }

    private function coverageScore(Attorney $attorney, string $location): int
    {
        $coverage = array_map(fn (string $area) => mb_strtolower($area), $attorney->coverage_areas ?? []);

        if ($coverage === []) {
            return 5;
        }

        if ($location === '' || in_array('all philippines', $coverage, true)) {
            return 10;
        }

        foreach ($coverage as $area) {
            if (str_contains($location, $area) || str_contains($area, $location)) {
                return 20;
            }
        }

        return 0;
    }

    private function terms(string $text): array
    {
        $stopWords = ['about', 'after', 'also', 'because', 'from', 'have', 'help', 'into', 'need', 'that', 'this', 'with', 'your'];
        $words = preg_split('/[^\pL\pN]+/u', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_unique(array_filter(
            $words,
            fn (string $word) => mb_strlen($word) >= 4 && ! in_array($word, $stopWords, true)
        )));
    }
}
