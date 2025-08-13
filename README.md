## Xero RFM MVP (Laravel 12, Blade)

This application links a user’s Xero organisation via OAuth 2.0, syncs recent invoices, and lays the groundwork for RFM (Recency, Frequency, Monetary) analysis. It uses a simple Blade UI (Breeze) with a clean dark/light theme and a minimal data model.

### Stack
- Laravel 12 (PHP 8.2+)
- Breeze (Blade) for auth scaffolding and layouts
- webfox/laravel-xero-oauth2 for OAuth + Xero SDK bindings
- XeroAPI/xero-php-oauth2 for Accounting & Identity APIs
- SQLite for local development (MySQL/MariaDB ready)

## Features
- Xero OAuth connect flow (consent → callback → persisted tokens/tenant)
- Dashboard showing Xero integration status (organisation name, tenant id, token expiry)
- Demo invoices page (last N days, read-only) with a basic table UI
- RFM Analysis placeholder page
- Dark mode–friendly layouts

## Directory map (key parts)
- `routes/web.php`
  - Public landing `GET /`
  - Authenticated: `GET /dashboard`, `GET /xero/connect`
  - Xero-only pages: `GET /demo/invoices`, `GET /rfm`
  - Compatibility callback: `GET /xero/callback` → forwards to package callback
- `app/Http/Controllers/XeroController.php`
  - `connect()` → redirect to Xero consent
  - `demoInvoices()` → fetch recent invoices via `AccountingApi`
- `app/Http/Middleware/EnsureXeroLinked.php` → enforces linking before Xero pages
- `app/Providers/AppServiceProvider.php`
  - Listens to `Webfox\Xero\Events\XeroAuthorized`
  - Stores encrypted access/refresh tokens, expiry, tenant id, and organisation name
- `resources/views/...`
  - `landing.blade.php` → welcome/login/register/enter-dashboard
  - `dashboard.blade.php` → Xero Integration card + quick links
  - `demo/invoices.blade.php` → invoices table (dark-mode friendly)
  - `rfm/index.blade.php` → placeholder

## Database schema (MVP)
- `xero_connections`
  - `user_id` (unique, 1 org per user)
  - `tenant_id` (string)
  - `org_name` (nullable string)
  - `access_token` (encrypted text)
  - `refresh_token` (encrypted text)
  - `expires_at` (timestamp)
- `clients`
  - `user_id`, `contact_id` (Xero GUID), `name`
- `xero_invoices`
  - `user_id`, `invoice_id` (GUID), `contact_id`
  - status, type, numbers, dates, amounts, currency
- `rfm_reports` (future)
  - Skeleton for client-level RFM metrics per period

## Xero app setup
Create a Xero “web app” in the developer portal.

- Scopes (must include OpenID to receive id_token):
  - `openid`, `profile`, `email`, `offline_access`, `accounting.transactions.read`
- Redirect URI (must match your .env exactly):
  - Example for PHP built-in server: `http://localhost:8080/xero/callback`

## .env (development)
SQLite for dev and full URL redirect for Xero. Ensure `APP_URL` and `XERO_REDIRECT_URI` host/port match how you run the server.

```dotenv
APP_NAME="Xero RFM App"
APP_ENV=local
APP_KEY=base64:changeme_if_you_regen
APP_DEBUG=true
APP_URL=http://localhost:8080

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local

MAIL_MAILER=log
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

XERO_CLIENT_ID=your_client_id
XERO_CLIENT_SECRET=your_client_secret
XERO_CREDENTIAL_DISK=local
XERO_REDIRECT_URI=http://localhost:8080/xero/callback
```

Switch to MySQL/MariaDB later by changing `DB_*` vars as usual.

## Install & bootstrap
```bash
composer install
cp .env.example .env   # if you don't already have one
php artisan key:generate

# SQLite file
mkdir -p database && touch database/database.sqlite

# Migrate schema
php artisan migrate

# Frontend
npm install
npm run build  # or npm run dev for hot reload
```

## Running the app

### Option A: Artisan
```bash
php artisan serve --host=127.0.0.1 --port=8080
```

### Option B: PHP built-in server (no artisan)
```bash
php -S localhost:8080 -t public
```

Front-end:
- Live reload during development: `npm run dev`
- One-off build (no watcher): `npm run build`

Ensure the Redirect URI in the Xero portal matches the URL you actually use (e.g. `http://localhost:8080/xero/callback`).

## How the OAuth flow works
1. User visits `Dashboard` and hits “Connect Xero,” or goes to `/xero/connect`.
2. App redirects to Xero consent using the package’s `AuthorizationController`.
3. Xero redirects back to `XERO_REDIRECT_URI` (full URL). We include a compatibility route at `/xero/callback` that forwards to the package callback.
4. Package exchanges code → emits `XeroAuthorized` with token + tenants.
5. Listener persists encrypted tokens, expiry, tenant id, and organisation name to `xero_connections`.
6. User is redirected to `dashboard`.

## Current user experience
- Landing: shows Log in / Register (or Enter dashboard + Log out when authenticated).
- Dashboard: shows “Xero Integration” card. If connected, shows Organisation name, Tenant ID, and token expiry, with a “Resync connection” action. Quick tiles link to Invoices and RFM Analysis.
- Invoices: last N days (selectable) ACCREC invoices, read-only table (dark-mode friendly). Data is also upserted into local DB (`clients`, `xero_invoices`).

## Clearing the database
- Rebuild schema and wipe data:
```bash
php artisan migrate:fresh
```

- For SQLite, full reset:
```bash
rm -f database/database.sqlite && touch database/database.sqlite
php artisan migrate
```
On Windows PowerShell:
```powershell
del database\database.sqlite
ni database\database.sqlite -ItemType File
php artisan migrate
```

## Troubleshooting
- 404 after consent: ensure your Xero Redirect URI and `.env` `XERO_REDIRECT_URI` are identical. We support `/xero/callback` and forward to the package callback.
- unauthorized_client / Invalid redirect_uri: clear config and fix the Redirect URI.
```bash
php artisan config:clear
```
- id_token missing error: ensure scopes include `openid profile email`.
- Port in use: change the port (and the Redirect URI) accordingly.

## Notes on tokens
- Access/refresh tokens are stored encrypted in DB.
- The package refreshes tokens automatically when expired.
- “Resync connection” simply runs the connect flow again; you can convert this to a silent refresh endpoint later if desired.

## Roadmap
- Compute and persist RFM metrics from `xero_invoices` into `rfm_reports`.
- Add filters, pagination, and exports to the Invoices page.
- Add per-tenant multi-org support (today: 1 org per user).

## License
MIT (see LICENSE if present).
