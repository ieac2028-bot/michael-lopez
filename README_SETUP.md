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

To view the demo case, register a client account using `demo@client.com`. Client registration and sign-in do not require email verification. After registering, save the one-time recovery codes shown on screen; you need a code to reset the password without email.

## Local administrative module

The prototype now includes client intake profiling, practice-area and coverage-based sample attorney suggestions, referral assignment, case acceptance timing, case status/outcome tracking, and an admin dashboard. The recorded testimony field is an internal demonstration note and is not shown in the client portal. Attorney ratings and availability are sample data, not real recommendations or appointment confirmations.

Create the first administrator after running migrations. The command asks for a name, email and a password (typed at a hidden prompt):

```bash
php artisan admin:create
```

If mail is configured, you can instead email the new admin a link to choose their own password:

```bash
php artisan admin:create --name="GPMO Admin" --email=admin@example.test --invite
```

Then visit `http://127.0.0.1:8000/login` and choose Administrator.

**Adding more administrators:** sign in as an administrator, open **Administrator accounts** on the dashboard, and enter the colleague's name and work email. This email-based setup requires an outgoing mail provider. The same section lets you deactivate an administrator (immediate loss of access, record kept) or reactivate them later. You cannot deactivate your own account, and there is no public admin sign-up.

Do not use real client or confidential case information in this prototype.

## Attorney accounts and authentication

Authentication uses Laravel sessions with separate `admin`, `attorney`, and
`client` roles. The role migration preserves existing administrators. No login
accounts are created for the fictional sample attorney profiles. From the admin
dashboard's **Attorney availability and accounts** section, enter an attorney's
email and an initial password beside the actual profile to create an individual account.
Share the password with the attorney through a secure channel. Attorneys
can view and update progress only for matters assigned to their linked profile.

Client password resets use one-time recovery codes. To email a temporary reset
code without SMTP, configure the Gmail API OAuth values in `.env`:

```dotenv
GMAIL_API_CLIENT_ID=your-oauth-client-id
GMAIL_API_CLIENT_SECRET=your-oauth-client-secret
GMAIL_API_REFRESH_TOKEN=your-offline-refresh-token
GMAIL_API_SENDER_ADDRESS=your-authorized-gmail-address@gmail.com
```

Enable the Gmail API in Google Cloud, create OAuth credentials, and authorize
the `https://www.googleapis.com/auth/gmail.send` scope with offline access so
Google issues a refresh token. The sender address must be the authorized Gmail
account or one of its configured send-as aliases. The app uses HTTPS requests
to refresh the access token and call Gmail's `users.messages.send` endpoint;
it does not connect to an SMTP server. After setting `.env`, run
`php artisan config:clear`. Keep all OAuth secrets in `.env`, never in source
control. Saved backup codes continue to work when Gmail API is not configured.

Each attorney account gets recovery codes displayed to the administrator when
it is created. For an existing account, an administrator can issue a new set
after verifying the account holder through a trusted channel:

```bash
php artisan account:recovery-codes client@example.com
```

This command replaces any unused backup codes and prints the new codes once. Other
email-based features, such as administrator invitations, still require a
separately configured outgoing mail provider. Require unique credentials; do not share staff
accounts. Multi-factor authentication is not configured yet.

## Project notes

- `public/css/app.css` contains the cream, navy, and orange portfolio-inspired styling.
- `public/js/app.js` drives the mobile navigation and FAQ chat interface.
- The FAQ assistant currently uses fixed keyword matching; AI FAQ support is a planned feature.
- Database records are sample data. The forms save to the local database and do not contact or schedule real attorneys.
- Do not enter real or confidential legal information into the prototype.

For a MySQL setup, set the `DB_*` values in `.env` before running migrations.
