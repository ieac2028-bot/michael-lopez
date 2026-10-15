# Abogadokonek

Standalone Laravel 12 prototype adapted from the supplied ZIP. It uses SQLite
for local development and serves its plain CSS and JavaScript from `public/`.

## Run locally

Requirements: PHP 8.2+ and Composer.

```powershell
composer install
php artisan migrate --seed
php artisan serve
```

Open `http://127.0.0.1:8000`. The seeded demo client-portal record uses:

- Email: `demo@client.com`
- Reference: `ABG-DEMO-0001`

To view the demo case, register a client account using `demo@client.com`, then verify that email. Configure SMTP or a local mail catcher in `.env` before registering and set `APP_URL` to the exact host used to open the site. Avoid the `log` mailer for client accounts because it writes verification messages and their links to application logs.

## Local administrative module

The prototype now includes client intake profiling, practice-area and coverage-based sample attorney suggestions, referral assignment, case acceptance timing, case status/outcome tracking, and an admin dashboard. The recorded testimony field is an internal demonstration note and is not shown in the client portal. Attorney ratings and availability are sample data, not real recommendations or appointment confirmations.

Create your own local administrator after migrations:

```bash
php artisan tinker
```

At the Tinker prompt, replace the email and password with your own values:

```php
$admin = App\Models\User::updateOrCreate(
    ['email' => 'admin@example.test'],
    ['name' => 'SafaLaw Admin', 'password' => Illuminate\Support\Facades\Hash::make('replace-with-a-strong-password'), 'is_admin' => true]
);
$admin->forceFill(['role' => 'admin'])->save();
```

Then visit `http://127.0.0.1:8000/login` and choose Administrator. Do not use real client or confidential case information in this prototype.

## Attorney accounts and authentication

Authentication uses Laravel sessions with separate `admin`, `attorney`, and
`client` roles. The role migration preserves existing administrators. No login
accounts are created for the fictional sample attorney profiles. From the admin
dashboard, enter an attorney's work email beside the actual profile to create
an individual account and email a time-limited password setup link. Attorneys
can view and update progress only for matters assigned to their linked profile.

All account types can request a password reset at sign in. A successful reset
link also verifies control of that email address. Configure SMTP or a local
mail catcher and set `APP_URL` before issuing attorney invitations or using
verification and password-reset emails. Require unique credentials; do not
share staff accounts. Multi-factor authentication is not configured yet.

## Project notes

- `public/css/app.css` contains the cream, navy, and orange portfolio-inspired styling.
- `public/js/app.js` drives the mobile navigation and FAQ chat interface.
- The FAQ assistant currently uses fixed keyword matching; AI FAQ support is a planned feature.
- Database records are sample data. The forms save to the local database and do not contact or schedule real attorneys.
- Do not enter real or confidential legal information into the prototype.

For a MySQL setup, set the `DB_*` values in `.env` before running migrations.
