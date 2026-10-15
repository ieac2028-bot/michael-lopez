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
        ['keys' => ['what is abogadokonek', 'what is this website', 'what does this site do', 'purpose of this website'], 'reply' => 'Abogadokonek is SAFALAW’s client-discovery and case-management project. This version demonstrates browsing practice areas, sample attorney suggestions, intake, and case-progress screens. Some workflows are prototypes and do not contact or assign real attorneys.'],
        ['keys' => ['how many attorneys', 'attorney profiles', 'lawyers listed'], 'reply' => 'The site currently shows 25 example attorney profiles. They are demonstration profiles, not confirmation of current staff, credentials, or availability.'],
        ['keys' => ['how does matching work', 'how are lawyers matched', 'how do you match', 'matching process', 'how does abogado'], 'reply' => 'The consultation demo can suggest sample attorney profiles from the selected practice area and location information entered. Profiles and availability are demonstration data; this is not a live referral or confirmed appointment.'],
        ['keys' => ['what practice areas', 'practice areas do you handle', 'services does the firm offer', 'legal services'], 'reply' => 'You can browse the practice-area categories and descriptions on the Practice Areas page. The categories are for browsing and do not determine which law applies to a real matter.', 'cta' => 'practice-areas'],
        ['keys' => ['free legal aid', 'legal aid', 'indigent', 'public attorney', 'pao', 'law school clinic', 'ngo'], 'reply' => 'The project concept includes a path to legal-aid providers such as PAO, law-school clinics, and NGOs for people who may need free assistance. Those providers are not connected or verified in this prototype, and only the provider can confirm eligibility.'],
        ['keys' => ['document checklist', 'checklist', 'requirements', 'what documents', 'documents do i need'], 'reply' => 'The planned guided intake can show a checklist based on the selected concern. This prototype cannot determine the documents needed for a real matter; confirm requirements with the lawyer or provider handling it.'],
        ['keys' => ['referral tracking', 'track a referral', 'track referral', 'case timeline', 'case progress', 'referral status', 'track my case', 'case status', 'check my matter'], 'reply' => 'The Client Portal demonstrates case-progress lookup using a verified client account and case reference. Its records are sample project data, not live SAFALAW case records.', 'cta' => 'client-portal'],
        ['keys' => ['verified lawyer', 'verify attorney', 'provider ranking', 'ranked', 'availability and location', 'coverage', 'how do i choose an attorney', 'can i choose a lawyer'], 'reply' => 'Attorney profiles and matching details on this site are sample data. Confirm an attorney’s identity, qualifications, availability, and engagement terms directly with the office before relying on a profile.'],
        ['keys' => ['is this ai', 'does the bot use ai', 'artificial intelligence', 'how does the assistant work'], 'reply' => 'This assistant uses scripted, keyword-based responses to explain the project and guide browsing. It is not connected to a generative AI model and cannot analyze a real legal problem.'],
        ['keys' => ['office hours', 'opening hours', 'business hours', 'what time do you open', 'what time do you close', 'when are you open'], 'reply' => 'The Contact page lists office hours as Monday–Friday, 9 AM–5 PM. Please contact the office directly to confirm holiday availability.'],
        ['keys' => ['location', 'where are you', 'where is the office', 'address', 'located', 'directions'], 'reply' => 'The office is listed at SAFA Building, 5858 Alfonso cor. Fermina Streets, Poblacion, Makati City 1210, Metro Manila.', 'cta' => 'contact'],
        ['keys' => ['practice area', 'what do you handle', 'what kind of case', 'specializ'], 'reply' => 'Browse the Practice Areas page to see the listed categories and descriptions. These help with navigation; they are not an assessment of a real legal matter.', 'cta' => 'practice-areas'],
        ['keys' => ['need to bring', 'what should i bring', 'prepare for', 'documents for consultation'], 'reply' => 'The website does not publish a document checklist for individual matters. Avoid sending sensitive documents through this prototype; ask the office what to prepare for your specific appointment.'],
        ['keys' => ['request a consultation', 'make an appointment', 'schedule a consultation', 'consultation appointment'], 'reply' => 'The consultation form demonstrates the intake flow and saves a sample request locally. It is not connected to the firm’s scheduling system, so contact the office directly to request an actual appointment.', 'cta' => 'contact'],
        ['keys' => ['request a demo', 'see a demo', 'demonstration'], 'reply' => 'The demo request page shows the project’s sample intake flow. Submissions are stored locally and are not sent to the firm.', 'cta' => 'consultation'],
        ['keys' => ['how soon', 'next available', 'wait time', 'how long before'], 'reply' => 'Attorney availability and response times are not live in this project. Please call or email the office for current availability.', 'cta' => 'contact'],
        ['keys' => ['call', 'phone number', 'contact number', 'landline', 'mobile number', 'how can i contact', 'contact the firm'], 'reply' => 'You can call +63 2 8897 0527 to 30 or +63 917 164 0184, or email safa@safalaw.com.ph. The Contact page also lists the office address and explains that its message form saves locally rather than emailing the firm.', 'cta' => 'contact'],
        ['keys' => ['privacy', 'confidential', 'secure', 'protect my information', 'personal information', 'how is my information handled', 'how is data used'], 'reply' => 'This is a project prototype. Its forms save data in the local project database and are not connected to SAFALAW’s live systems. Please do not enter confidential facts, identity numbers, or sensitive documents here.'],
        ['keys' => ['fee', 'cost', 'how much', 'price', 'rate', 'consultation fee', 'free consultation'], 'reply' => 'Fees and consultation terms are not listed in this project. Please contact the office directly to ask about current rates and payment arrangements.', 'cta' => 'contact'],
        ['keys' => ['recogniz', 'affiliat', 'member of', 'accredit'], 'reply' => 'This project does not claim affiliations or accreditations. Please verify attorney credentials independently.'],
        ['keys' => ['general question', 'quick question', 'just a question'], 'reply' => 'You can use the Contact page to view the office’s phone and email details. Its message form saves to the project database but is not connected to the firm’s email.', 'cta' => 'contact'],
    ];

    public function respond(Request $request): JsonResponse
    {
        $data = $request->validate(['message' => ['required', 'string', 'max:500']]);
        $text = mb_strtolower(trim($data['message']));

        if ($this->containsAny($text, self::BOOKING_KEYS)) {
            return response()->json([
                'reply' => 'You can view the sample consultation form to see how this project captures a request. It will not book an actual appointment.',
                'cta' => ['label' => 'View sample request form →', 'route' => route('consultation')],
                'quickReplies' => ['How do I request a consultation?', 'Where is the office?', 'How do I contact the firm?'],
            ]);
        }

        if ($this->containsAny($text, self::ADVICE_TRIGGERS)) {
            return response()->json([
                'reply' => "I can't give legal advice, assess eligibility, or predict how a matter turns out. This prototype does not connect you to an attorney. Use the sample inquiry page only to explore the form flow; for a real concern, speak directly with a qualified lawyer or legal-aid provider.",
                'cta' => ['label' => 'View sample consultation form →', 'route' => route('consultation')],
                'quickReplies' => ['Browse practice areas', 'How do I contact the firm?', 'How do I find free legal aid?'],
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
                    'cta' => isset($entry['cta']) ? [
                        'label' => match ($entry['cta']) {
                            'contact' => 'View contact information →',
                            'consultation' => 'View demo request page →',
                            'client-portal' => 'Open Client Portal →',
                            default => 'Browse practice areas →',
                        },
                        'route' => route($entry['cta']),
                    ] : null,
                    'quickReplies' => ['Browse practice areas', 'How do I request a consultation?', 'How do I track my case?', 'What are the office hours?'],
                ]);
            }
        }

        return response()->json([
            'reply' => "I don't have a scripted answer for that. I can guide you to a starting practice-area category and explain the planned matching, legal-aid, checklist, and referral-tracking features. This prototype is not a real law office and cannot provide legal advice.",
            'cta' => ['label' => 'Explore sample practice areas →', 'route' => route('practice-areas')],
            'quickReplies' => ['Help me find a practice area', 'Browse practice areas', 'How do I request a consultation?', 'How do I contact the firm?'],
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
