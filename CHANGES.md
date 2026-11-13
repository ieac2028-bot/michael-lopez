# Admin accounts without editing the database

Copy these files over the matching paths in your Laravel project (same folders), then run:

```bash
php artisan migrate
php artisan view:clear
php artisan test --filter=AdminAccountTest   # optional but recommended
```

| File | What it does |
|---|---|
| `database/migrations/2026_11_12_000001_add_deactivated_at_to_users_table.php` | **New.** Adds `users.deactivated_at`; marks existing admins as set up. |
| `app/Models/User.php` | Adds `deactivated_at`, `isDeactivated()`, `hasAdminRole()`, scopes. A deactivated admin fails every admin check and does not become a client. |
| `app/Http/Controllers/AdminAccountController.php` | **New.** Add admin (emails a setup link), deactivate, reactivate, resend link. |
| `app/Console/Commands/CreateAdmin.php` | **New.** `php artisan admin:create` for the very first admin (hidden password prompt, or `--invite`). |
| `app/Http/Controllers/ConsultationController.php` | A deactivated admin no longer counts as "Admin is online" in the client chat. |
| `app/Http/Controllers/AdminController.php` | Passes the admin list to the dashboard. |
| `app/Http/Middleware/RejectUnknownRequestFields.php` | Allows the new form fields (the project rejects unknown fields by route name). |
| `routes/web.php` | Four new routes inside the existing admin-only group. |
| `resources/views/admin/dashboard.blade.php` | New "Administrator accounts" section and quick link. |
| `tests/Feature/AdminAccountTest.php` | **New.** 13 tests. |
| `README_SETUP.md`, `docs/request-body-schemas.md` | Replace the tinker steps; document the new routes. |

Notes
- Not run here: this environment has no PHP, so the code and tests are unexecuted. Run the tests before demoing.
- If you already changed any of the patched files (routes, dashboard, AdminController), merge by hand instead of overwriting.
- Password setup links expire after 60 minutes (`config/auth.php`); use "Resend link" if one lapses.
