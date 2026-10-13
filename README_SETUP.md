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

## Local administrative module

The prototype now includes client intake profiling, practice-area and coverage-based sample attorney suggestions, referral assignment, case acceptance timing, case status/outcome tracking, and an admin dashboard. The recorded testimony field is an internal demonstration note and is not shown in the client portal. Attorney ratings and availability are sample data, not real recommendations or appointment confirmations.

Create your own local administrator after migrations:

```bash
php artisan tinker
```

At the Tinker prompt, replace the email and password with your own values:

```php
App\Models\User::updateOrCreate(
    ['email' => 'admin@example.test'],
    ['name' => 'SafaLaw Admin', 'password' => Illuminate\Support\Facades\Hash::make('replace-with-a-strong-password'), 'is_admin' => true]
);
```

Then visit `http://127.0.0.1:8000/admin/login`. Do not use real client or confidential case information in this prototype.

## Project notes

- `public/css/app.css` contains the cream, navy, and orange portfolio-inspired styling.
- `public/js/app.js` drives the mobile navigation and FAQ chat interface.
- The FAQ assistant currently uses fixed keyword matching; AI FAQ support is a planned feature.
- Database records are sample data. The forms save to the local database and do not contact or schedule real attorneys.
- Do not enter real or confidential legal information into the prototype.

For a MySQL setup, set the `DB_*` values in `.env` before running migrations.
