<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Scoped, rule-based Abogadokonek client orientation assistant.
 *
 * Boundary: answers basic platform and sample-content questions only. It must NEVER attempt to
 * give legal advice, predict case outcomes, or substitute for an
 * attorney's judgment — anything that looks like that is redirected to
 * the clearly labeled sample inquiry form.
 *
 * This is intentionally NOT backed by a generative model — it is a fixed
 * keyword-matched rule set. A future AI FAQ assistant is only a planned feature.
 */
class ChatbotController extends Controller
{
    private const BOOKING_KEYS = [
        'book now', 'book a consultation', 'i want to book', 'yes book',
        'schedule now', 'ready to book', 'sign me up', 'i want a consultation',
        "let's book", 'lets book',
    ];

    private const ADVICE_TRIGGERS = [
        'will i win', 'am i going to win', 'chances of winning', 'what should i do',
        'should i sue', 'should i file', 'is this legal', 'is this illegal',
        'am i guilty', 'what are my chances', 'give me advice', 'legal advice',
        'what happens if i', 'can i go to jail', 'penalty for', 'draft a',
        'write a demand letter', 'write my', 'how do i win', 'interpret this contract',
        'review my contract', 'is this a good settlement', 'is my trademark infringed',
    ];

    private const AREA_FINDER_KEYS = [
        'help me find a practice area', 'help me choose a practice area',
        'help me find the right practice area', 'i do not know what practice area',
        "i don't know what practice area", 'what kind of lawyer do i need',
        'which area should i choose', 'find the right lawyer', 'classify my concern',
    ];

    private const AREA_STARTING_POINTS = [
        ['keys' => ['work or employment', 'labor concern', 'workplace issue'], 'area' => 'Labor Law', 'slug' => 'labor-law'],
        ['keys' => ['family or inheritance', 'family concern', 'inheritance concern'], 'area' => 'Family Relations and Estate Planning', 'slug' => 'family-relations-and-estate-planning'],
        ['keys' => ['property or land', 'land concern', 'property concern'], 'area' => 'Property', 'slug' => 'property'],
        ['keys' => ['business or contracts', 'business concern', 'contract concern'], 'area' => 'Commercial Law', 'slug' => 'commercial-law'],
        ['keys' => ['tax concern', 'taxation'], 'area' => 'Taxation', 'slug' => 'taxation'],
        ['keys' => ['immigration concern', 'immigration'], 'area' => 'Immigration Law', 'slug' => 'immigration-law'],
        ['keys' => ['intellectual property', 'copyright or trademark'], 'area' => 'Intellectual Property', 'slug' => 'intellectual-property'],
    ];

    private const FAQ = [
        ['keys' => ['how does matching work', 'how are lawyers matched', 'how do you match', 'matching process', 'how does abogado'], 'reply' => 'AbogadoKonek is designed to clarify a visitor’s general concern, then suggest relevant verified lawyer or legal-aid profiles using practice area, service coverage, and availability. The current prototype has no live provider directory, so it does not make real matches.'],
        ['keys' => ['free legal aid', 'legal aid', 'indigent', 'public attorney', 'pao', 'law school clinic', 'ngo'], 'reply' => 'The project concept includes a path to legal-aid providers such as PAO, law-school clinics, and NGOs for people who may need free assistance. Those providers are not connected or verified in this prototype, and only the provider can confirm eligibility.'],
        ['keys' => ['document checklist', 'checklist', 'requirements', 'what documents', 'documents do i need'], 'reply' => 'The planned guided intake can show a checklist based on the selected concern. This prototype cannot determine the documents needed for a real matter; confirm requirements with the lawyer or provider handling it.'],
        ['keys' => ['referral tracking', 'track a referral', 'track referral', 'case timeline', 'case progress', 'referral status'], 'reply' => 'The project is designed to show referral progress on a timeline and record updates. The current demo is not connected to real referrals, cases, or client records.'],
        ['keys' => ['verified lawyer', 'verify attorney', 'provider ranking', 'ranked', 'availability and location', 'coverage'], 'reply' => 'The proposed matching considers provider verification, relevant specialization, geographic service coverage, and availability. These are planned criteria; this prototype does not contain live verified profiles or availability.'],
        ['keys' => ['is this ai', 'does the bot use ai', 'artificial intelligence', 'how does the assistant work'], 'reply' => 'This assistant uses scripted, keyword-based responses to explain the project and guide browsing. It is not connected to a generative AI model and cannot analyze a real legal problem.'],
        ['keys' => ['hour', 'open', 'close', 'schedule'], 'reply' => 'This is a student project prototype; real office hours are not configured.'],
        ['keys' => ['location', 'where are you', 'address', 'located'], 'reply' => 'Abogadokonek is a student project prototype. No office address is configured.'],
        ['keys' => ['practice area', 'what do you handle', 'what kind of case', 'specializ'], 'reply' => 'Browse the Practice Areas page to see the sample categories in this prototype. Verify any attorney’s qualifications directly before seeking legal services.'],
        ['keys' => ['need to bring', 'what should i bring', 'prepare for', 'documents for consultation'], 'reply' => 'This prototype does not schedule real consultations. If you meet with a qualified attorney, ask them directly what documents to bring.'],
        ['keys' => ['how soon', 'next available', 'wait time', 'how long before'], 'reply' => 'This prototype does not have real attorney availability or response times configured.'],
        ['keys' => ['call', 'phone number', 'contact number', 'landline', 'mobile number'], 'reply' => 'No real contact number is configured in this student project prototype.'],
        ['keys' => ['case status', 'track my case', 'portal', 'check my matter'], 'reply' => 'The Client Portal page is a demonstration only; it is not connected to real client records.'],
        ['keys' => ['fee', 'cost', 'how much', 'price', 'rate'], 'reply' => 'This prototype does not provide real legal services or fee information. Confirm any fees directly with a verified attorney.'],
        ['keys' => ['recogniz', 'affiliat', 'member of', 'accredit'], 'reply' => 'This project does not claim affiliations or accreditations. Please verify attorney credentials independently.'],
        ['keys' => ['general question', 'quick question', 'just a question'], 'reply' => 'This prototype has a Contact page for sample questions and a Book a Consultation page to demonstrate the request flow. Neither page currently connects you to a real law office.'],
    ];

    public function respond(Request $request): JsonResponse
    {
        $data = $request->validate(['message' => ['required', 'string', 'max:500']]);
        $text = mb_strtolower(trim($data['message']));

        if ($this->containsAny($text, self::BOOKING_KEYS)) {
            return response()->json([
                'reply' => 'You can view the sample consultation form to see how this project captures a request. It will not book an actual appointment.',
                'cta' => ['label' => 'View sample request form →', 'route' => route('consultation')],
                'quickReplies' => ['How does matching work?', 'Help me find a practice area'],
            ]);
        }

        if ($this->containsAny($text, self::ADVICE_TRIGGERS)) {
            return response()->json([
                'reply' => "I can't give legal advice, assess eligibility, or predict how a matter turns out. This prototype does not connect you to an attorney. Use the sample inquiry page only to explore the form flow; for a real concern, speak directly with a qualified lawyer or legal-aid provider.",
                'cta' => ['label' => 'View sample consultation form →', 'route' => route('consultation')],
                'quickReplies' => ['Help me find a practice area', 'Can I find free legal aid?'],
            ]);
        }

        if ($this->containsAny($text, self::AREA_FINDER_KEYS)) {
            return response()->json([
                'reply' => 'I can help you choose a starting category for browsing, not determine the legal classification of a real case. Pick the general topic that sounds closest:',
                'cta' => null,
                'quickReplies' => ['Work or employment', 'Family or inheritance', 'Property or land', 'Business or contracts', 'Tax concern', 'Immigration concern', 'Intellectual property', 'Something else'],
            ]);
        }

        foreach (self::AREA_STARTING_POINTS as $startingPoint) {
            if ($this->containsAny($text, $startingPoint['keys'])) {
                $area = \App\Models\PracticeArea::where('slug', $startingPoint['slug'])->first();
                $route = route('practice-areas') . ($area ? '#practice-area-' . $area->id : '');

                return response()->json([
                    'reply' => 'For browsing, ' . $startingPoint['area'] . ' may be a useful starting category. This is only a navigation suggestion, not a legal assessment; a qualified provider would need to review your actual concern.',
                    'cta' => ['label' => 'Explore ' . $startingPoint['area'] . ' →', 'route' => $route],
                    'quickReplies' => ['How does matching work?', 'Can I find free legal aid?', 'Show all practice areas'],
                ]);
            }
        }

        if ($this->containsAny($text, ['something else', 'show all practice areas'])) {
            return response()->json([
                'reply' => 'You can browse all listed categories and their descriptions. The list helps with navigation only; it does not determine which law applies to a real matter.',
                'cta' => ['label' => 'Browse all practice areas →', 'route' => route('practice-areas')],
                'quickReplies' => ['How does matching work?', 'Can I find free legal aid?', 'What documents might be needed?'],
            ]);
        }

        foreach (self::FAQ as $entry) {
            if ($this->containsAny($text, $entry['keys'])) {
                return response()->json([
                    'reply' => $entry['reply'],
                    'cta' => null,
                    'quickReplies' => ['Help me find a practice area', 'How does matching work?', 'Can I find free legal aid?'],
                ]);
            }
        }

        return response()->json([
            'reply' => "I don't have a scripted answer for that. I can guide you to a starting practice-area category and explain the planned matching, legal-aid, checklist, and referral-tracking features. This prototype is not a real law office and cannot provide legal advice.",
            'cta' => ['label' => 'Explore sample practice areas →', 'route' => route('practice-areas')],
            'quickReplies' => ['Help me find a practice area', 'How does matching work?', 'Can I find free legal aid?'],
        ]);
    }

    private function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }
}
