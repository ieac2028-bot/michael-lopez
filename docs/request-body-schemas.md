# Request body schemas

Application web routes use `RejectUnknownRequestFields` to reject unknown top-level keys, uploaded files, and nested values before controller validation. Controller validation remains responsible for required fields, formats, lengths, and allowed values. Laravel's `_token` and `_method` form fields are accepted as transport metadata.

## Routes with request bodies

| Route | Allowed body fields | Required/value validation |
|---|---|---|
| `POST /api/chat` (`chat.respond`) | `message` | Required string, max 500. |
| `POST /client-portal/lookup` (`client-portal.lookup`) | `email`, `reference_no` | Both required; email format; reference string max 50. |
| `POST /contact` (`contact.store`) | `first_name`, `last_name`, `email`, `message` | Names required strings max 75; email required and valid max 150; message required string max 2000. |
| `POST /book-consultation` (`consultation.store`) | `full_name`, `email`, `phone`, `client_type`, `client_location`, `practice_area_id`, `legal_profile`, `client_requirements`, `preferred_date`, `preferred_time`, `message` | All required. Name string max 150; email valid max 150; phone string max 30; client type one of `individual`, `business`, `organization`; location max 120; practice area must exist; legal profile and requirements strings max 2000; preferred date today or later; preferred time one of the two listed slots; message string max 2000. |
| `POST /admin/login` (`admin.login.store`) | `email`, `password`, `remember` | Email and password required; email valid; password string; remember optional boolean. Authentication then verifies the account is an admin. |
| `POST /admin/logout` (`admin.logout`) | *(none)* | Authenticated session required. |
| `PATCH /admin/inquiries/{inquiry}` (`admin.inquiries.update`) | `status`, `attorney_id` | Status required and one of `new`, `reviewing`, `clarifying`, `referred`, `accepted`, `declined`, `closed`; attorney ID optional nullable integer that must exist and match the inquiry practice area when set. Admin access required. |
| `POST /admin/inquiries/{inquiry}/accept` (`admin.inquiries.accept`) | *(none)* | Admin access required; controller checks consultation type, assigned attorney availability/area, and duplicate case. |
| `PATCH /admin/cases/{caseRecord}` (`admin.cases.update`) | `status`, `outcome`, `testimony` | Status required and one of `active`, `on_hold`, `resolved`, `closed`; outcome optional string max 150; testimony optional string max 5000. Admin access required. |
| `PATCH /admin/attorneys/{attorney}` (`admin.attorneys.update`) | `is_available`, `coverage_areas` | Availability required boolean; coverage optional string max 500. Admin access required. |

The remaining application routes have an empty body schema: `GET /`, `GET /about-us`, `GET /attorneys`, `GET /practice-areas`, `GET /the-firm`, `GET /client-portal`, `GET /contact`, `GET /book-consultation`, `GET /login`, `GET /admin/login`, and `GET /admin`. Laravel's built-in local storage serve/upload routes are disabled in `config/filesystems.php`; this application currently has no file-upload workflow. The `/up` health route also accepts no application body.

## Fields clients must never set

No route accepts `user_id`, `userId`, `owner_id`, `is_admin`, `id`, `created_at`, or `updated_at`. The schema middleware rejects them everywhere a request body is defined. Never add these to public form schemas. Laravel's session `user_id` is framework-managed and is not a request-body field.

Public contact and consultation forms also must not set `status`, `type`, `attorney_id`, or generated case fields such as `reference_no`, `accepted_at`, `timeline`, `resolved_at`, `outcome`, and `testimony`. These are assigned/managed server-side. The client portal lookup intentionally accepts `reference_no` only as a lookup credential, not as a value that creates or changes a case. Admin-only schemas allow the explicitly listed operational fields; admin authorization is enforced by the route middleware.
