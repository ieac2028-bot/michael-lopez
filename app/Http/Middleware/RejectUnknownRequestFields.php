<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RejectUnknownRequestFields
{
    /**
     * Accepted form/JSON fields by route. Laravel transport fields are allowed
     * separately below; controller validation still enforces required fields
     * and value types.
     */
    private const FIELDS = [
        'home' => [],
        'about-us' => [],
        'attorneys' => [],
        'practice-areas' => [],
        'firm' => [],
        'legal-insights' => [],
        'client-portal' => [],
        'contact' => [],
        'consultation' => [],
        'login' => [],
        'admin.login' => [],
        'admin.dashboard' => [],
        'chat.respond' => ['message', 'language'],
        'chat.translate' => ['message'],
        'contact.store' => ['first_name', 'last_name', 'email', 'message'],
        'consultation.store' => [
            'body',
        ],
        'admin.login.store' => ['email', 'password', 'remember'],
        'client.login.store' => ['email', 'password', 'remember'],
        'attorney.login.store' => ['email', 'password', 'remember'],
        'client.register.store' => ['name', 'email', 'password', 'password_confirmation'],
        'password.email' => ['email'],
        'password.update' => ['email', 'recovery_code', 'password', 'password_confirmation'],
        'attorney.cases.update' => ['status'],
        'attorney.matching-availability.update' => ['accepting_matches'],
        'attorney.profile.update' => ['primary_practice_area_id', 'budget_min', 'budget_max', 'contact_email', 'contact_phone', 'specialization_note', 'years_experience', 'coverage_areas', 'spoken_languages', 'other_languages'],
        'attorney.inquiries.respond' => ['decision', 'consultation_fee'],
        'consultation.payment.sent' => ['payment_reference', 'acknowledge_terms'],
        'consultation.payment.cancel' => [],
        'consultation.payment.refund-review' => [],
        'admin.inquiries.payment.confirm' => ['consultation_fee', 'payment_instructions', 'payment_refund_terms', 'payment_expires_at'],
        'admin.inquiries.payment.verify-start' => [],
        'admin.inquiries.payment.verify' => ['payment_reference', 'meeting_access_url', 'consultation_access_details'],
        'consultation.messages.store' => ['body'],
        'admin.inquiries.messages.store' => ['body'],
        'admin.inquiries.intake.update' => [
            'phone', 'client_type', 'client_location', 'nationality', 'practice_area_id', 'legal_profile',
            'client_requirements', 'budget_range', 'consultation_method', 'preferred_date', 'preferred_time',
        ],
        'admin.inquiries.match' => [
            'phone', 'client_type', 'client_location', 'nationality', 'practice_area_id', 'legal_profile',
            'client_requirements', 'budget_range', 'consultation_method', 'preferred_date', 'preferred_time',
        ],
        'admin.attorneys.account.store' => ['attorney_profile_id', 'display_name', 'attorney_email', 'password', 'password_confirmation'],
        'admin.admins.store' => ['name', 'email'],
        'admin.admins.deactivate' => [],
        'admin.admins.reactivate' => [],
        'admin.admins.invite.resend' => [],
        'admin.logout' => [],
        'client.logout' => [],
        'admin.inquiries.update' => ['status', 'attorney_id'],
        'admin.cases.update' => ['status', 'outcome', 'testimony'],
        'admin.attorneys.update' => ['is_available', 'coverage_areas', 'budget_min', 'budget_max', 'contact_email', 'contact_phone'],
    ];

    private const FRAMEWORK_FIELDS = ['_token', '_method'];

    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()?->getName();
        $body = $request->isJson()
            ? $request->json()->all()
            : $request->request->all();

        $allowed = array_merge(self::FIELDS[$routeName] ?? [], self::FRAMEWORK_FIELDS);
        $unknown = array_diff(array_keys($body), $allowed);
        $hasFiles = $request->allFiles() !== [];
        $arrayFields = $routeName === 'attorney.profile.update'
            ? ['spoken_languages']
            : [];
        $hasNestedValues = collect($body)->contains(function ($value, $key) use ($arrayFields) {
            if (in_array($key, $arrayFields, true) && is_array($value)) {
                return collect($value)->contains(fn ($item) => ! is_scalar($item));
            }

            return ! is_scalar($value) && $value !== null;
        });

        if ($unknown !== [] || $hasFiles || $hasNestedValues) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'The request body contains unsupported fields or values.'], 422);
            }

            abort(422, 'The request body contains unsupported fields or values.');
        }

        return $next($request);
    }
}
