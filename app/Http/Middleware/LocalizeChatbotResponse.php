<?php

namespace App\Http\Middleware;

use App\Services\ChatbotTranslationService;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LocalizeChatbotResponse
{
    public function __construct(private readonly ChatbotTranslationService $translator)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $language = (string) $request->input('language', 'en');
        if (! in_array($language, ChatbotTranslationService::LANGUAGES, true)) {
            $language = 'en';
        }

        $request->merge([
            'message' => $this->translator->normalizeInput((string) $request->input('message', ''), $language),
        ]);

        $response = $next($request);
        if ($language === 'en' || ! $response instanceof JsonResponse) return $response;

        $payload = $response->getData(true);
        if (isset($payload['reply']) && is_string($payload['reply'])) {
            $payload['sourceReply'] = $payload['reply'];
            $payload['reply'] = $this->translator->translateReply($payload['reply'], $language);
        }
        if (isset($payload['quickReplies']) && is_array($payload['quickReplies'])) {
            $payload['quickReplies'] = array_map(
                fn ($label) => $this->translator->quickReply((string) $label, $language),
                $payload['quickReplies']
            );
        }
        if (isset($payload['cta']['label']) && is_string($payload['cta']['label'])) {
            $payload['cta']['label'] = $this->translator->quickReply($payload['cta']['label'], $language);
        }
        $response->setData($payload);

        return $response;
    }
}
