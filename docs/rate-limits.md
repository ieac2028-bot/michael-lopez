# Route rate limits

| Route | Limit | Exceeded response |
|---|---|---|
| `POST /admin/login` (`admin.login.store`) | 5 attempts per minute per client IP | HTTP `429 Too Many Requests`, `Retry-After` header, and body `Too many login attempts. Try again in {N} seconds.` For JSON clients, the body is `{"message":"Too many login attempts. Try again in {N} seconds."}`. |
| `POST /client/login` (`client.login.store`) | 5 attempts per minute per client IP | Laravel HTTP `429 Too Many Requests` response with `Retry-After`. |
| `POST /client/register` (`client.register.store`) | 5 attempts per hour per client IP | Laravel HTTP `429 Too Many Requests` response with `Retry-After`. |
| `POST /client-portal/lookup` (`client-portal.lookup`) | 10 attempts per minute per authenticated user and client IP | Laravel HTTP `429 Too Many Requests` response with `Retry-After`. |
| `POST /attorney/login` (`attorney.login.store`) | 5 attempts per minute per client IP | Laravel HTTP `429 Too Many Requests` response with `Retry-After`. |
| `POST /forgot-password` (`password.email`) | 5 attempts per minute per client IP | Laravel HTTP `429 Too Many Requests` response with `Retry-After`. |
| `POST /reset-password` (`password.update`) | 5 attempts per minute per client IP | Laravel HTTP `429 Too Many Requests` response with `Retry-After`. |
| `POST /email/verification-notification` (`verification.send`) | 6 attempts per minute per authenticated user and client IP | Laravel HTTP `429 Too Many Requests` response with `Retry-After`. |
| `GET /email/verify/{id}/{hash}` (`verification.verify`) | 6 attempts per minute per authenticated user and client IP | Laravel HTTP `429 Too Many Requests` response with `Retry-After`. |

The named login and registration limiters are registered in `AppServiceProvider`. Login limits are per IP to slow credential guessing without locking a legitimate account based on an attacker's repeated attempts. Portal lookup limits are scoped to the authenticated account and IP.

## No current route for these categories

- Paid API calls: no route calls a paid API. The chatbot endpoint (`POST /api/chat`) uses fixed local keyword matching and makes no external API request.

When a paid-provider integration is added, apply a named route limiter before enabling the route. Choose the actual allowance based on the provider's quota and endpoint cost, and return HTTP `429` with a `Retry-After` header when exceeded.
