# Route rate limits

| Route | Limit | Exceeded response |
|---|---|---|
| `POST /admin/login` (`admin.login.store`) | 5 attempts per minute per client IP | HTTP `429 Too Many Requests`, `Retry-After` header, and body `Too many login attempts. Try again in {N} seconds.` For JSON clients, the body is `{"message":"Too many login attempts. Try again in {N} seconds."}`. |

The named `admin-login` limiter is registered in `AppServiceProvider`. The limiter is per IP to slow credential guessing without locking a legitimate account based on an attacker's repeated attempts.

## No current route for these categories

- Signup: no signup route exists.
- Password reset: no password-reset routes exist.
- Paid API calls: no route calls a paid API. The chatbot endpoint (`POST /api/chat`) uses fixed local keyword matching and makes no external API request.

When signup, password reset, or a paid-provider integration is added, apply a named route limiter before enabling the route. Choose the actual allowance based on the provider's quota and endpoint cost, and return HTTP `429` with a `Retry-After` header when exceeded.
