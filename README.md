# Xero RFM Analysis Platform

A comprehensive Laravel application that integrates with Xero to perform RFM (Recency, Frequency, Monetary) analysis on client data. The platform provides multi-organization support, invoice management, RFM scoring, and advanced analytics capabilities.

## 🚀 Features

### Core Functionality
- **Multi-Organization Xero Integration**: Connect and manage multiple Xero organizations per user
- **OAuth 2.0 Authentication**: Secure Xero API access with automatic token refresh
- **Invoice Synchronization**: Import and manage sales invoices from Xero
- **RFM Analysis**: Calculate and track Recency, Frequency, and Monetary scores for clients
- **Historical Data Tracking**: Monthly snapshots for trend analysis
- **Invoice Exclusion System**: Mark specific invoices to exclude from RFM calculations
- **Subscription Management**: GoCardless integration for premium plans (Free, Pro, Pro+)
- **Dark/Light Mode UI**: Modern, responsive interface with Tailwind CSS

### Pages & Functionality
- **Dashboard**: Organization overview, connection status, and quick navigation
- **Organizations**: Multi-org management with switching capabilities
- **Memberships**: Subscription management with GoCardless integration
- **Invoices**: View, filter, and manage imported invoices with exclusion controls
- **RFM Scores**: Current and historical RFM leaderboard with filtering
- **RFM Reports**: Generate custom reports and analytics (in development)
- **RFM Analysis**: Advanced analytics and trend analysis (in development)

## 🛠 Tech Stack

- **Backend**: Laravel 12 (PHP 8.4+)
- **Frontend**: Blade templates with Tailwind CSS
- **Authentication**: Laravel Breeze
- **Database**: SQLite (development), MySQL/MariaDB ready
- **Xero Integration**: webfox/laravel-xero-oauth2
- **Payment Processing**: GoCardless Pro API
- **Build Tool**: Vite

## 💳 GoCardless Integration Setup

### Prerequisites
1. **GoCardless Account**: Sign up at [gocardless.com](https://gocardless.com)
2. **API Access**: Get your access token from the GoCardless dashboard
3. **Webhook URL**: Set up webhook endpoint for payment notifications

### Environment Variables
Add these to your `.env` file:

```env
# GoCardless Configuration
GOCARDLESS_ACCESS_TOKEN=your_access_token_here
GOCARDLESS_ENVIRONMENT=sandbox
GOCARDLESS_WEBHOOK_SECRET=your_webhook_secret_here
GOCARDLESS_CREDITOR_ID=your_creditor_id_here

# Optional: Plan-specific IDs (if using GoCardless plans)
GOCARDLESS_PRO_PLAN_ID=your_pro_plan_id
GOCARDLESS_PRO_PLUS_PLAN_ID=your_pro_plus_plan_id
```

### Subscription Plans
The system supports three subscription tiers:

- **Free Plan**: £0/month - Basic RFM analysis and insights
- **Pro Plan**: £5.99/month - Enhanced insights and recommendations
- **Pro+ Plan**: £11.99/month - AI-powered insights and chat features

### Webhook Configuration
1. Set your webhook URL to: `https://yourdomain.com/webhooks/gocardless`
2. Configure webhook events for: `subscriptions`, `mandates`, `payments`
3. Use the webhook secret for signature verification

### Testing
- Use GoCardless sandbox environment for testing
- Test payment flows with sandbox bank details
- Verify webhook processing with test events

## 📊 Database Schema

### Core Tables

#### `users`
- Standard Laravel user authentication
- Supports multiple Xero organizations
- `subscription_plan` - Current subscription plan (free, pro, pro_plus)
- `gocardless_subscription_id` - GoCardless subscription identifier
- `subscription_status` - Subscription status (active, cancelled, etc.)
- `subscription_ends_at` - Subscription end date (nullable)

#### `xero_connections`
- `user_id` - Foreign key to users
- `tenant_id` - Xero organization identifier
- `org_name` - Organization display name
- `access_token` - Encrypted OAuth access token
- `refresh_token` - Encrypted OAuth refresh token
- `expires_at` - Token expiration timestamp
- `is_active` - Boolean flag for active organization

#### `clients`
- `user_id` - Foreign key to users
- `tenant_id` - Xero organization identifier
- `contact_id` - Xero contact GUID
- `name` - Client/contact name

#### `xero_invoices`
- `user_id` - Foreign key to users
- `tenant_id` - Xero organization identifier
- `invoice_id` - Xero invoice GUID
- `contact_id` - Foreign key to clients
- `invoice_number` - Invoice number
- `reference` - Invoice reference
- `status` - Invoice status (AUTHORISED, PAID, etc.)
- `type` - Invoice type (ACCREC for sales invoices)
- `date` - Invoice date
- `due_date` - Payment due date
- `sub_total` - Invoice subtotal
- `total_tax` - Tax amount
- `total` - Total invoice amount
- `currency_code` - Currency code
- `line_amount_types` - Line amount type
- `updated_date_utc` - Last update timestamp

#### `excluded_invoices`
- `user_id` - Foreign key to users
- `invoice_id` - Foreign key to xero_invoices
- `created_at` - Exclusion timestamp

#### `rfm_reports`
- `user_id` - Foreign key to users
- `client_id` - Foreign key to clients
- `snapshot_date` - Date of RFM calculation
- `r_score` - Recency score (0-10)
- `f_score` - Frequency score (0-10)
- `m_score` - Monetary score (0-10)
- `rfm_score` - Overall RFM score (0-10)
- `months_since_last` - Months since last transaction (nullable)
- `txn_count` - Number of transactions in period
- `monetary_sum` - Total revenue in period
- `last_txn_date` - Date of last transaction

## 🔗 API Endpoints

### Authentication & Core
- `GET /` - Landing page
- `GET /dashboard` - Main dashboard
- `GET /profile` - User profile management
- `POST /profile` - Update profile

### Subscription Management
- `GET /memberships` - View subscription plans
- `POST /memberships/subscribe` - Subscribe to a plan
- `POST /memberships/cancel` - Cancel subscription
- `GET /memberships/payment` - Payment page for paid plans
- `POST /webhooks/gocardless` - GoCardless webhook endpoint

### Xero Integration
- `GET /xero/connect` - Initiate Xero OAuth flow
- `GET /xero/callback` - OAuth callback handler

### Organization Management
- `GET /organizations` - List user's Xero organizations
- `POST /organizations/{id}/switch` - Switch active organization
- `DELETE /organizations/{id}/disconnect` - Disconnect organization

### Invoice Management
- `GET /invoices` - View and filter invoices
- `POST /invoices/sync` - Sync invoices from Xero
- `POST /invoices/{id}/exclude` - Exclude invoice from RFM calculations
- `DELETE /invoices/{id}/exclude` - Remove invoice exclusion

### RFM Analysis
- `GET /rfm` - RFM Scores leaderboard
- `POST /rfm/sync` - Calculate current and historical RFM scores
- `GET /rfm/reports` - RFM Reports page (in development)
- `GET /rfm/reports/generate` - Generate RFM reports (in development)
- `GET /rfm/analysis` - RFM Analysis tools (in development)
- `GET /rfm/analysis/trends` - Trend analysis (in development)

## 🏗 Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── XeroController.php          # Xero OAuth and API integration
│   │   ├── OrganizationController.php  # Multi-org management
│   │   ├── InvoicesController.php      # Invoice management and sync
│   │   ├── RfmController.php           # RFM scores and calculations
│   │   ├── RfmReportsController.php    # Report generation (in dev)
│   │   ├── RfmAnalysisController.php   # Advanced analytics (in dev)
│   │   └── ProfileController.php       # User profile management
│   └── Middleware/
│       └── EnsureXeroLinked.php        # Enforce Xero connection
├── Models/
│   ├── User.php                        # User model with Xero relations
│   ├── XeroConnection.php              # Xero organization connections
│   ├── Client.php                      # Client/contact data
│   ├── XeroInvoice.php                 # Invoice data
│   ├── ExcludedInvoice.php             # Excluded invoice tracking
│   └── RfmReport.php                   # RFM calculation results
└── Services/
    └── Rfm/
        └── RfmCalculator.php           # RFM calculation logic

resources/views/
├── layouts/
│   └── navigation.blade.php            # Main navigation
├── dashboard.blade.php                 # Dashboard overview
├── organizations/
│   └── index.blade.php                 # Organization management
├── invoices/
│   └── index.blade.php                 # Invoice listing and filters
├── rfm/
│   ├── index.blade.php                 # RFM Scores leaderboard
│   ├── reports/
│   │   └── index.blade.php             # Reports page (in dev)
│   └── analysis/
│       ├── index.blade.php             # Analysis tools (in dev)
│       └── trends.blade.php            # Trend analysis (in dev)
└── landing.blade.php                   # Public landing page
```

## 🔧 Installation & Setup

### Prerequisites
- PHP 8.4+
- Composer
- Node.js & npm
- Xero Developer Account

### 1. Clone and Install Dependencies
```bash
git clone <repository-url>
cd xero-rfm
composer install
npm install
```

### 2. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Configure your `.env` file:
```dotenv
APP_NAME="Xero RFM Analysis"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8080

DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

# Xero Configuration
XERO_CLIENT_ID=your_xero_client_id
XERO_CLIENT_SECRET=your_xero_client_secret
XERO_CREDENTIAL_DISK=local
XERO_REDIRECT_URI=http://localhost:8080/xero/callback

# GoCardless Configuration
GOCARDLESS_ACCESS_TOKEN=your_gocardless_access_token
GOCARDLESS_ENVIRONMENT=sandbox
GOCARDLESS_WEBHOOK_SECRET=your_webhook_secret
GOCARDLESS_CREDITOR_ID=your_creditor_id
```

### 3. Database Setup
```bash
# Create SQLite database
mkdir -p database
touch database/database.sqlite

# Run migrations
php artisan migrate
```

### 4. Build Frontend Assets
```bash
npm run build
# or for development with hot reload:
npm run dev
```

### 5. Start the Application
```bash
# Option A: Laravel's built-in server
php artisan serve --host=127.0.0.1 --port=8080

# Option B: PHP built-in server
php -S localhost:8080 -t public
```

## 🔐 Xero App Configuration

### Required Scopes
- `openid` - OpenID Connect authentication
- `profile` - User profile information
- `email` - Email address access
- `offline_access` - Refresh token access
- `accounting.transactions.read` - Read invoice data

### Redirect URI
Configure in Xero Developer Portal:
- `http://localhost:8080/xero/callback`

## 📈 RFM Analysis Methodology

### Score Calculation
- **Recency (R)**: `10 - months_since_last_transaction` (minimum 0)
- **Frequency (F)**: Number of invoices in past 12 months (capped at 10)
- **Monetary (M)**: Total revenue normalized to 0-10 scale using min-max scaling
- **Overall RFM**: `(R + F + M) / 3`

### Data Processing
- Rolling 12-month analysis window
- Sales invoices only (ACCREC type)
- Excluded invoices are filtered out
- Monthly snapshots for historical tracking
- Zero scores for clients with no transactions

## 🔄 Database Management

### Reset Database
```bash
# Fresh migration (drops all data)
php artisan migrate:fresh

# Complete SQLite reset
rm -f database/database.sqlite
touch database/database.sqlite
php artisan migrate
```

### Windows PowerShell
```powershell
del database\database.sqlite
ni database\database.sqlite -ItemType File
php artisan migrate
```

## 🚨 Troubleshooting

### Common Issues

**404 after OAuth consent**
- Verify `XERO_REDIRECT_URI` matches Xero app configuration exactly
- Check port numbers and protocol (http/https)

**"Invalid redirect_uri" error**
- Clear config cache: `php artisan config:clear`
- Ensure redirect URI is identical in Xero portal and .env

**"id_token missing" error**
- Verify scopes include `openid profile email`

**Port conflicts**
- Change port in both server command and Xero redirect URI
- Update `.env` `APP_URL` accordingly

### Token Management
- Access/refresh tokens are encrypted in database
- Automatic token refresh handled by Xero package
- "Resync connection" re-runs OAuth flow

## 🔮 Roadmap

### Completed Features
- ✅ Multi-organization Xero integration
- ✅ Invoice synchronization and management
- ✅ RFM score calculation and historical tracking
- ✅ Invoice exclusion system
- ✅ Organization switching
- ✅ Dark/light mode UI

### In Development
- 🔄 RFM Reports generation
- 🔄 Advanced analytics and trend analysis
- 🔄 Chart.js integration for visualizations
- 🔄 Export functionality (PDF, CSV)

### Planned Features
- 📋 Email notifications for score changes
- 📋 Automated monthly RFM calculations
- 📋 Client segmentation analysis
- 📋 Predictive churn modeling
- 📋 API endpoints for external integrations
- 📋 Bulk invoice operations
- 📋 Advanced filtering and search

## 📝 License

MIT License - see LICENSE file for details.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📞 Support

For issues and questions:
- Check the troubleshooting section above
- Review Laravel and Xero API documentation
- Open an issue in the repository
