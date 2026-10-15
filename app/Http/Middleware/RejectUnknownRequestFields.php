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
        'chat.respond' => ['message'],
        'client-portal.lookup' => ['reference_no'],
        'contact.store' => ['first_name', 'last_name', 'email', 'message'],
        'consultation.store' => [
            'full_name', 'email', 'phone', 'client_type', 'client_location',
            'practice_area_id', 'legal_profile', 'client_requirements',
            'preferred_date', 'preferred_time', 'message',
        ],
        'admin.login.store' => ['email', 'password', 'remember'],
        'client.login.store' => ['email', 'password', 'remember'],
        'attorney.login.store' => ['email', 'password', 'remember'],
        'client.register.store' => ['name', 'email', 'password', 'password_confirmation'],
        'password.email' => ['email'],
        'password.update' => ['token', 'email', 'password', 'password_confirmation'],
        'attorney.cases.update' => ['status'],
        'admin.attorneys.account.store' => ['email'],
        'admin.logout' => [],
        'client.logout' => [],
        'verification.send' => [],
        'admin.inquiries.update' => ['status', 'attorney_id'],
        'admin.inquiries.accept' => [],
        'admin.cases.update' => ['status', 'outcome', 'testimony'],
        'admin.attorneys.update' => ['is_available', 'coverage_areas'],
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
        $hasNestedValues = collect($body)->contains(fn ($value) => ! is_scalar($value) && $value !== null);

        if ($unknown !== [] || $hasFiles || $hasNestedValues) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'The request body contains unsupported fields or values.'], 422);
            }

            abort(422, 'The request body contains unsupported fields or values.');
        }

        return $next($request);
    }
}
