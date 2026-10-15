# Request body schemas

Application web routes use `RejectUnknownRequestFields` to reject unknown top-level keys, uploaded files, and nested values before controller validation. Controller validation remains responsible for required fields, formats, lengths, and allowed values. Laravel's `_token` and `_method` form fields are accepted as transport metadata.

## Routes with request bodies

| Route | Allowed body fields | Required/value validation |
|---|---|---|
| `POST /api/chat` (`chat.respond`) | `message` | Required string, max 500. |
| `POST /client-portal/lookup` (`client-portal.lookup`) | `reference_no` | Required string max 50. Requires a verified client account; the case email is taken from the signed-in account, never from request input. |
| `POST /contact` (`contact.store`) | `first_name`, `last_name`, `email`, `message` | Names required strings max 75; email required and valid max 150; message required string max 2000. |
| `POST /book-consultation` (`consultation.store`) | `full_name`, `email`, `phone`, `client_type`, `client_location`, `practice_area_id`, `legal_profile`, `client_requirements`, `preferred_date`, `preferred_time`, `message` | All required. Name string max 150; email valid max 150; phone string max 30; client type one of `individual`, `business`, `organization`; location max 120; practice area must exist; legal profile and requirements strings max 2000; preferred date today or later; preferred time one of the two listed slots; message string max 2000. |
| `POST /admin/login` (`admin.login.store`) | `email`, `password`, `remember` | Email and password required; email valid; password string; remember optional boolean. Authentication then verifies the account is an admin. |
| `POST /client/login` (`client.login.store`) | `email`, `password`, `remember` | Email and password required; email valid max 150; password string max 255; remember optional boolean. Admin accounts are rejected. |
| `POST /attorney/login` (`attorney.login.store`) | `email`, `password`, `remember` | Email and password required; the account must have attorney role and a linked attorney profile. |
| `POST /client/register` (`client.register.store`) | `name`, `email`, `password`, `password_confirmation` | Name required string max 150; email required valid max 150 and unique; password confirmed, minimum 12 characters including mixed case, a number, and a symbol. The server creates the account with `is_admin=false` and sends email verification. |
| `POST /forgot-password` (`password.email`) | `email` | Required valid email max 150. Rate limited; sending requires configured mail transport. |
| `POST /reset-password` (`password.update`) | `token`, `email`, `password`, `password_confirmation` | Reset token required; email valid; password confirmed, minimum 12 characters with mixed case, a number, and a symbol. Successful email-link reset verifies the account email. |
| `POST /admin/attorneys/{attorney}/account` (`admin.attorneys.account.store`) | `email` | Required unique email. Admin only; creates attorney role, links the profile, and emails a password setup link. |
| `PATCH /attorney-portal/cases/{caseRecord}` (`attorney.cases.update`) | `status` | Status required and one of `active`, `on_hold`, `resolved`, `closed`; case must be assigned to the signed-in attorney profile or returns 404. |
| `POST /attorney-portal/logout` (`attorney.logout`) | *(none)* | Authenticated attorney account required. |
| `POST /admin/logout` (`admin.logout`) | *(none)* | Authenticated administrator session required. |
| `POST /client/logout` (`client.logout`) | *(none)* | Authenticated non-admin session required. |
| `POST /email/verification-notification` (`verification.send`) | *(none)* | Authenticated non-admin session required; rate limited. |
| `PATCH /admin/inquiries/{inquiry}` (`admin.inquiries.update`) | `status`, `attorney_id` | Status required and one of `new`, `reviewing`, `clarifying`, `referred`, `accepted`, `declined`, `closed`; attorney ID optional nullable integer that must exist and match the inquiry practice area when set. Admin access required. |
| `POST /admin/inquiries/{inquiry}/accept` (`admin.inquiries.accept`) | *(none)* | Admin access required; controller checks consultation type, assigned attorney availability/area, and duplicate case. |
| `PATCH /admin/cases/{caseRecord}` (`admin.cases.update`) | `status`, `outcome`, `testimony` | Status required and one of `active`, `on_hold`, `resolved`, `closed`; outcome optional string max 150; testimony optional string max 5000. Admin access required. |
| `DELETE /admin/cases/{caseRecord}` (`admin.cases.destroy`) | *(none)* | Admin middleware required; no deletion feature exists, so the route always returns 404. |
| `PATCH /admin/attorneys/{attorney}` (`admin.attorneys.update`) | `is_available`, `coverage_areas` | Availability required boolean; coverage optional string max 500. Admin access required. |

The remaining application routes have an empty body schema: `GET /`, `GET /about-us`, `GET /attorneys`, `GET /practice-areas`, `GET /the-firm`, `GET /legal-insights`, `GET /client-portal`, `GET /contact`, `GET /book-consultation`, `GET /login`, `GET /client/login`, `GET /client/register`, `GET /attorney/login`, `GET /attorney-portal`, `GET /forgot-password`, `GET /reset-password/{token}`, `GET /email/verify`, `GET /email/verify/{id}/{hash}`, `GET /admin/login`, and `GET /admin`. Laravel's built-in local storage serve/upload routes are disabled in `config/filesystems.php`; this application currently has no file-upload workflow. The `/up` health route also accepts no application body.

## Fields clients must never set

No public route accepts `user_id`, `userId`, `owner_id`, `is_admin`, `id`, `created_at`, or `updated_at`. The schema middleware rejects them on request bodies. Never add these to public form schemas. Laravel's session `user_id` is framework-managed and is not a request-body field.

Public contact and consultation forms also must not set `status`, `type`, `attorney_id`, or generated case fields such as `reference_no`, `accepted_at`, `timeline`, `resolved_at`, `outcome`, and `testimony`. These are assigned/managed server-side. The client portal lookup intentionally accepts `reference_no` only as a lookup credential, not as a value that creates or changes a case. Admin-only schemas allow the explicitly listed operational fields; admin authorization is enforced by the route middleware.
