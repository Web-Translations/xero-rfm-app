# Xero RFM Analysis Platform

A Laravel application that integrates with Xero to perform RFM (Recency, Frequency, Monetary) analysis on client data. It provides multi‑organisation support, invoice synchronisation, configurable RFM scoring, subscription management via GoCardless, and optional AI insights via OpenAI.

## Overview

- Multi‑organisation Xero OAuth2 with automatic token refresh
- Invoice import and management with exclusion controls
- RFM scoring snapshots with historical trends
- Memberships (Free, Pro, Pro+) via GoCardless subscriptions
- Optional AI-generated narrative insights (configurable)

## Architecture

- Backend: Laravel 12 (PHP 8.2+)
- Frontend: Blade + Tailwind CSS, Vite
- Auth: Laravel Breeze
- Database: SQLite by default (MySQL/MariaDB ready)
- Integrations: `webfox/laravel-xero-oauth2`, `gocardless/gocardless-pro`, OpenAI via Guzzle

## Directory Structure (selected)

```
app/
  Http/
    Controllers/            # Xero, Invoices, RFM, Memberships, etc.
    Middleware/             # AutoRefreshXeroToken, EnsureXeroLinked
  Models/                   # User, XeroConnection, XeroInvoice, Client, RfmReport, ...
  Services/
    GoCardlessService.php   # GoCardless API wrapper (subscriptions, webhook handling)
    Narrative/AiInsightService.php  # OpenAI-backed (optional) narrative insights
    Rfm/RfmCalculator.php   # Core RFM computation and snapshots
    Xero/DatabaseCredentialManager.php # Token storage/refresh (used by package)
config/
  xero.php                  # Xero client/scopes/redirect
  gocardless.php            # Plans and credentials
  ai.php                    # AI provider and model settings
routes/
  web.php, auth.php         # Pages, API endpoints, webhooks
resources/views/            # Blade templates (dashboard, rfm, invoices, memberships, ...)
database/migrations/        # Schema (users, xero_connections, xero_invoices, rfm_reports, ...)
```

## Dependencies

- PHP (composer.json)
  - laravel/framework ^12.0
  - webfox/laravel-xero-oauth2 ^6.1
  - gocardless/gocardless-pro ^7.2
  - barryvdh/laravel-dompdf ^3.1, elibyy/tcpdf-laravel ^11.5 (PDF)
  - laravel/tinker ^2.10.1
  - Dev: laravel/breeze, pestphp/pest (+ plugin), laravel/pint, laravel/sail, nunomaduro/collision

- Node (package.json)
  - devDependencies: tailwindcss, @tailwindcss/forms, @tailwindcss/vite, vite, laravel-vite-plugin, axios, alpinejs, postcss, autoprefixer, concurrently
  - dependencies: chart.js

Locations:
- Composer packages: `composer.json`
- Node packages: `package.json`
- Integration config: `config/xero.php`, `config/gocardless.php`, `config/ai.php`

## Environment Configuration (.env)

Create `.env` (see template below) and run `php artisan key:generate`. Never commit real secrets.

```dotenv
# Application
APP_NAME="Xero RFM App"
APP_ENV=local
APP_KEY=base64:generate_with_artisan
APP_DEBUG=true
APP_URL=http://localhost:8080

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=debug

# Database (default: SQLite)
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

# Runtime (simple local defaults)
CACHE_STORE=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local

# Mail (logs to storage/logs/laravel.log)
MAIL_MAILER=log
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Xero OAuth2
XERO_CLIENT_ID=your_xero_client_id
XERO_CLIENT_SECRET=your_xero_client_secret
XERO_CREDENTIAL_DISK=local
XERO_REDIRECT_URI=http://localhost:8080/xero/callback

# GoCardless
GOCARDLESS_ACCESS_TOKEN=your_gocardless_token
GOCARDLESS_ENVIRONMENT=sandbox   # sandbox|live
GOCARDLESS_WEBHOOK_SECRET=your_webhook_secret
GOCARDLESS_CREDITOR_ID=your_creditor_id_optional
GOCARDLESS_SUCCESS_URL=http://localhost:8080/memberships/success

# OpenAI (optional)
OPENAI_API_KEY=your_openai_key_optional
AI_PROVIDER=openai
AI_INSIGHTS_ENABLED=true

# Frontend
VITE_APP_NAME="${APP_NAME}"

# Sessions / HTTPS (adjust for production)
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=false      # true when behind HTTPS
SESSION_SAME_SITE=lax            # consider none for cross-site iframes
FORCE_HTTPS=false                # true in production with HTTPS
```

## Setup

1) Clone and install
```bash
git clone <repository-url>
cd xero-rfm
composer install
npm install
```

2) Bootstrap
```bash
cp .env.example .env  # if present, else create using the template above
php artisan key:generate
mkdir -p database && type NUL > database\database.sqlite  # Windows
php artisan migrate
```

3) Run (development)
```bash
# Option A: single processes
php artisan serve --host=127.0.0.1 --port=8080
npm run dev

# Option B: combined
composer run dev  # serves app, queue listener, logs, and Vite
```

4) Build assets (production)
```bash
npm run build
```

## Integrations

### Xero

- Package: `webfox/laravel-xero-oauth2`
- Config: `config/xero.php`
- Scopes: `openid`, `profile`, `email`, `offline_access`, `accounting.transactions.read`
- Middleware: `auto.refresh.xero` (access token refresh), `EnsureXeroLinked` (enforce connection)
- Routes:
  - `GET /xero/connect` (`xero.connect`) – initiate OAuth
  - Callback handled by package route `xero.auth.callback` (compat shim: `GET /xero/callback` redirects)

Setup steps (Xero Developer portal):
1) Create a new app in the Xero Developer portal.
2) Add the redirect URI exactly as in your `.env` `XERO_REDIRECT_URI` (example: `http://localhost:8080/xero/callback`). The value must match character-for-character, including protocol, hostname, and port.
3) Copy the Client ID and Client Secret into `.env` as `XERO_CLIENT_ID` and `XERO_CLIENT_SECRET`.
4) Ensure required scopes are configured in `config/xero.php` (defaults already set).
5) Start the app and visit `GET /xero/connect` to complete the OAuth flow.

Redirect URI matching notes:
- If you change local ports or domains (e.g., using ngrok for demos), update both Xero app settings and `.env` `XERO_REDIRECT_URI` to the exact same URL.
- After changing `.env`, run `php artisan config:clear` to reload configuration.

### GoCardless

- SDK: `gocardless/gocardless-pro`
- Config: `config/gocardless.php` (plans, environment, credentials)
- Success redirect: `.env` `GOCARDLESS_SUCCESS_URL` (e.g., `/memberships/success`)
- Webhook endpoint: `POST /webhooks/gocardless` (CSRF disabled for this route)
- Service: `App\Services\GoCardlessService` handles customers, redirect flows, mandates, subscriptions, and webhook processing
- Plans: defined in `config/gocardless.php` (`free`, `pro`, `pro_plus`)

Setup steps (GoCardless Dashboard):
1) Create/get an Access Token in the GoCardless dashboard. Use Sandbox for demos.
2) Set `.env` values: `GOCARDLESS_ACCESS_TOKEN`, `GOCARDLESS_ENVIRONMENT=sandbox|live`, optionally `GOCARDLESS_CREDITOR_ID`.
3) Configure the success URL in `.env` as `GOCARDLESS_SUCCESS_URL` (example: `http://localhost:8080/memberships/success`).
4) Configure Webhooks in GoCardless dashboard with URL `https://your-domain/webhooks/gocardless` (use your ngrok/host). Set the Webhook Secret and copy it to `.env` as `GOCARDLESS_WEBHOOK_SECRET`.
5) Verify signatures: ensure your server is reachable via HTTPS for production; for local development, signature verification is gracefully handled and logged.

Important matching notes:
- The webhook secret configured in GoCardless must match `.env` `GOCARDLESS_WEBHOOK_SECRET`.
- The webhook URL in the GoCardless dashboard must be the public URL for your app (ngrok or production domain) and match your deployment environment.

### OpenAI (optional)

- Config: `config/ai.php` (`AI_PROVIDER`, `OPENAI_API_KEY`, model, limits)
- Service: `App\Services\Narrative\AiInsightService` (falls back to deterministic text if disabled or on error)
- Toggle: set `AI_INSIGHTS_ENABLED=false` to disable outbound API calls

Setup steps:
1) Create an API key in the OpenAI platform.
2) Add `OPENAI_API_KEY` to `.env`. Optionally configure `OPENAI_MODEL`, `OPENAI_MAX_TOKENS`, and `OPENAI_TEMPERATURE` in `.env` to override defaults.
3) Keep keys secret and do not commit `.env`.

## Pages and Routes (high level)

- Public: `GET /` (landing), `GET /terms`, `GET /privacy`
- Auth: Breeze routes in `routes/auth.php` (login, register, password reset, verification)
- Dashboard: `GET /dashboard`
- Organisations: `GET /organisations`, switch/disconnect actions
- Memberships: `GET /memberships`, `GET /memberships/manage`, subscribe/cancel, payment flow, `GET /memberships/success`
- Invoices: list/sync/exclude and RFM timeline data
- RFM: `GET /rfm`, `POST /rfm/sync`, reports and PDF endpoints, analysis pages
- Webhooks: `POST /webhooks/gocardless`

## Database

Key tables (see migrations): `users` (Breeze), `xero_connections`, `clients`, `xero_invoices`, `excluded_invoices`, `rfm_configurations`, `rfm_reports`, `gocardless_customers`, `gocardless_subscription_events`, `gocardless_payment_events`.

Run migrations: `php artisan migrate`

## Testing and Quality

- Tests: Pest/PHPUnit – run `composer test`
- Linting: Laravel Pint – run `vendor/bin/pint`

## Production Notes

- Set `APP_URL` to your HTTPS domain and enable `FORCE_HTTPS=true`
- Use secure cookies: `SESSION_SECURE_COOKIE=true`, consider `SESSION_SAME_SITE=none` if embedding cross-site
- Use a robust database (MySQL/PostgreSQL) and update `DB_*`
- Configure `QUEUE_CONNECTION` (e.g., `redis`) and `CACHE_STORE` in production
- Set GoCardless to `live` and update webhook URL to `https://your-domain/webhooks/gocardless`
- Ensure `XERO_REDIRECT_URI` matches the production URL exactly

## Demo Release Note

For this demo release, all features are currently available on the Free plan, and new users are automatically onboarded to the Free plan. The membership payment flow is functional, but keep GoCardless in sandbox mode for ease of demonstration. If you later switch to production, update `GOCARDLESS_ENVIRONMENT=live`, set real credentials, and update webhook and success URLs to your HTTPS domain.

## Troubleshooting

- OAuth callback issues: clear config cache (`php artisan config:clear`) and verify redirect URIs and scopes
- GoCardless webhook signature errors: confirm correct `GOCARDLESS_WEBHOOK_SECRET` and HTTPS endpoint
- Token expiry: automatic refresh is handled; if refresh token expired, reconnect Xero via `/xero/connect`

## License

MIT License. See `LICENSE`.
