
# RFM Reporting & Agent — Integration Plan (Laravel + SQLite + Browsershot)

> **Goal:** Generate on-demand RFM reports from the existing `rfm_reports` snapshot table, ship a deterministic version first, and then plug in an AI narrative/agent that explains *why* things changed. Reports mirror the sections in your historical Word reports (headline KPIs, Top 50/10, concentration, movers, dormant/lost, actions) with flexible comparison periods, configurable RFM calculation windows, and Browsershot PDF generation.

---

## 0) Assumptions & Scope

- **Framework:** Laravel (PHP)
- **DB:** SQLite (can swap to MySQL/Postgres later with no logic change)
- **Data source:** `rfm_reports` snapshots (one row per `user_id` × `client_id` × `snapshot_date`)
- **Execution:** On-demand (user chooses snapshot date + comparison period + RFM window)
- **Output:** HTML (Blade) and PDF via Browsershot (Chrome-based with Chart.js support)
- **Numbers:** One deterministic source of truth (a single PHP service)
- **Narrative:** Pluggable: deterministic text first; OpenAI LLM narrative later
- **Comparison:** Flexible periods (monthly, quarterly, yearly, custom date ranges)
- **RFM Window:** Configurable lookback periods (6, 10, 12, 18, 24 months)

### RFM Definition (Configurable Implementation)

Each client is scored on three dimensions over a configurable rolling period (scores 0–10):

1. **Recency (R):**
   - Time since last invoice (in months).
   - **Score:** `R = max(0, 10 - months_since_last)`

2. **Frequency (F):**
   - Number of invoices in the configurable period.
   - **Score (capped):** `F = min(10, invoices_in_period)`

3. **Monetary (M):**
   - Total revenue = sum of invoice **subtotals** over the configurable period.
   - **Score (min–max normalized to 0–10):**
     ```
     M = (ClientTotal - MinTotal) / (MaxTotal - MinTotal) * 10
     ```

**Overall RFM (0–10):**  
`RFM = (R + F + M) / 3`

> Note: The snapshot table stores `r_score`, `f_score`, `m_score`, and `rfm_score`. We **do not recompute** them for reporting; we read what the snapshot provided. For configurable periods, we'll need to add a `rfm_window_months` field to track the calculation period used.

---

## 1) Data Model & Indexes

### Enhanced table structure
```sql
-- Existing table (already optimized)
CREATE TABLE rfm_reports (
  id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  user_id INTEGER NOT NULL,
  client_id INTEGER NOT NULL,
  snapshot_date DATE NOT NULL,
  rfm_window_months INTEGER DEFAULT 12, -- NEW: Track calculation period
  txn_count INTEGER NOT NULL,
  monetary_sum NUMERIC NOT NULL,
  last_txn_date DATE,
  months_since_last INTEGER,
  r_score INTEGER NOT NULL,
  f_score INTEGER NOT NULL,
  m_score INTEGER NOT NULL,
  rfm_score NUMERIC NOT NULL,
  created_at DATETIME,
  updated_at DATETIME
);

-- NEW: RFM calculation configuration per user
CREATE TABLE rfm_configurations (
  id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  user_id INTEGER NOT NULL,
  tenant_id VARCHAR(255) NOT NULL,
  default_rfm_window INTEGER DEFAULT 12,
  default_comparison_period VARCHAR(20) DEFAULT 'monthly',
  created_at DATETIME,
  updated_at DATETIME,
  UNIQUE(user_id, tenant_id)
);
```

### Recommended indexes (existing + new)
- `(user_id, snapshot_date)` ✅ Already exists
- `(user_id, snapshot_date, client_id)` ✅ Already exists
- `(user_id, client_id)` ✅ Already exists
- `(user_id, rfm_window_months, snapshot_date)` NEW
- `(user_id, tenant_id)` on rfm_configurations

---

## 2) Enhanced KPIs & Diagnostics

### Core KPIs per snapshot (vs flexible comparison period)
- **Revenue (L12M):** `SUM(monetary_sum)`
- **AOV:** `SUM(monetary_sum) / NULLIF(SUM(txn_count),0)`
- **Avg RFM (overall)** and **Avg RFM for Top‑50** (by `monetary_sum`)
- **Shares:** Top‑10 and Top‑50 revenue shares; **customers to reach 80%** of revenue
- **Counts:** Active customers; **New** (first‑ever active at this snapshot); **Returned** (not active comparison period but active before); **Lost** (active comparison period, not active now); **Dormant ≥6m** (`months_since_last >= 6`)

### NEW: Advanced KPIs & Metrics
- **Customer Lifetime Value (CLV):** `SUM(monetary_sum) / COUNT(DISTINCT client_id)`
- **RFM Segments:** High-Value (RFM 8-10), Mid-Value (RFM 5-7), Low-Value (RFM 2-4), At-Risk (RFM 0-1)
- **Churn Rate:** `(Lost customers / Previous active customers) * 100`
- **Retention Rate:** `((Current active - New) / Previous active) * 100`
- **Revenue Concentration:** Gini coefficient for revenue distribution
- **RFM Score Distribution:** Histogram of RFM scores across customer base
- **Top Customer Contribution:** Revenue share of top 5, 10, 25 customers
- **Average Order Frequency:** `SUM(txn_count) / COUNT(DISTINCT client_id)`
- **Revenue per Customer:** `SUM(monetary_sum) / COUNT(DISTINCT client_id)`
- **Customer Acquisition Cost Proxy:** Revenue from new customers vs total marketing spend (if available)

### Enhanced Diagnostics (explaining *why* things changed)
- **Revenue decomposition** (approximate): Separate ΔRevenue into
  - `volume_effect = (T_curr - T_prev) * P_prev` (orders effect)
  - `aov_effect    = (P_curr - P_prev) * T_curr` (ticket size effect)
  - `mix_effect    = ΔR - volume_effect - aov_effect` (customer mix)
  where `T = txn_count`, `P = AOV`, `R = revenue`.
- **Recency spike:** `% of active clients with months_since_last ≤ 1`, plus a flag if `avg RFM ↑` while `revenue ↓`.
- **Concentration shift:** ΔTop‑10/50 share and Δ"customers to reach 80%" (who moved in/out).
- **Churn analysis:** lost and dormant ≥6m with last spend/seen, churn velocity trends
- **Movers analysis:** Top ΔRFM ± and Top Δ£ ±, movement between RFM segments
- **Seasonal patterns:** Month-over-month, quarter-over-quarter trends
- **Customer cohort analysis:** Performance by customer acquisition period

---

## 3) Laravel Components

### Routes (on‑demand with flexible configuration)
```
GET  /reports/rfm?date=YYYY-MM-DD&compare=monthly&window=12        → HTML report
GET  /reports/rfm?date=YYYY-MM-DD&compare=quarterly&window=10      → HTML report  
GET  /reports/rfm?date=YYYY-MM-DD&compare=yearly&window=18         → HTML report
GET  /reports/rfm?date=YYYY-MM-DD&compare=custom&from=YYYY-MM-DD&to=YYYY-MM-DD&window=24 → HTML report
GET  /reports/rfm.pdf?date=YYYY-MM-DD&compare=monthly&window=12    → PDF download
POST /agent/rfm                                                      → (Phase 3) AI narrative / Q&A
GET  /rfm/config                                                     → RFM configuration settings
POST /rfm/config                                                     → Update RFM configuration
```

### Services
- **`RfmTools`**
  - `computeKpis($userId, $asOf, $comparisonPeriod, $rfmWindow)` → KPIs + comparison data
  - `getComparisonSnapshot($userId, $asOf, $comparisonPeriod, $rfmWindow)` → Previous period data
  - `breakdownRevenueChange($userId, $asOf, $kpis, $comparisonData)`
  - `concentrationChange($userId, $asOf, $kpis, $comparisonData)`
  - `recencyAnalysis($userId, $asOf, $kpis, $comparisonData)`
  - `moversAnalysis($userId, $asOf, $comparisonData)`
  - `churnAnalysis($userId, $asOf, $comparisonData)`
  - `segmentAnalysis($userId, $asOf, $kpis)` → RFM segment distribution
  - `cohortAnalysis($userId, $asOf, $comparisonData)` → Customer cohort performance
  - `deriveFindings($kpis, $diagnostics)` → structured tags

- **`ComparisonPeriodResolver`**
  - `resolveComparisonDate($asOf, $period)` → Previous period date
  - `getAvailableSnapshots($userId, $rfmWindow)` → Available dates for window
  - `validateComparisonPeriod($asOf, $comparisonDate)` → Ensure valid comparison

- **`RfmConfigurationManager`**
  - `getUserConfiguration($userId, $tenantId)` → User's RFM settings
  - `updateConfiguration($userId, $tenantId, $config)` → Save user preferences
  - `getDefaultConfiguration()` → System defaults

- **Narrative engine (pluggable)**
  - Interface: `NarrativeWriter::compose(array $payload): string`
  - Implementations:
    - `DeterministicNarrativeWriter` (Blade/string template)
    - `OpenAiNarrativeWriter` (OpenAI GPT-4 via Guzzle)
    - `LlmNarrativeWriterOllama` (self‑hosted via HTTP)

- **`ReportRenderer`**
  - Blade → HTML
  - HTML → PDF: **Browsershot** (Chrome-based; supports Chart.js/modern CSS)

- **`ReportStore`**
  - Persist `metrics.json`, `diagnostics.json`, HTML/PDF path, and meta (user, snapshot, comparison period, RFM window, model used).

### Controllers
- `RfmReportController@show` (HTML) / `@download` (PDF)
- `RfmAgentController@generate` (OpenAI narrative; optional chat/Q&A later)
- `RfmConfigurationController@index` / `@update` (RFM settings)

---

## 4) Rendering with Browsershot

### Browsershot Configuration
```php
// config/reports.php
return [
    'pdf' => [
        'driver' => 'browsershot',
        'node_binary' => env('NODE_BINARY', '/usr/bin/node'),
        'npm_binary' => env('NPM_BINARY', '/usr/bin/npm'),
        'chrome_path' => env('CHROME_PATH', '/usr/bin/google-chrome'),
        'options' => [
            'format' => 'A4',
            'margin_top' => '0.5in',
            'margin_right' => '0.5in', 
            'margin_bottom' => '0.5in',
            'margin_left' => '0.5in',
            'print_background' => true,
            'prefer_css_page_size' => true,
        ]
    ],
    'charts' => [
        'enabled' => true,
        'library' => 'chart.js',
        'colors' => [
            'primary' => '#3B82F6',
            'secondary' => '#10B981',
            'warning' => '#F59E0B',
            'danger' => '#EF4444',
        ]
    ]
];
```

### Blade Layout Structure
```blade
{{-- resources/views/reports/rfm.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>RFM Report - {{ $asOf }} ({{ $rfmWindow }}m window)</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Print-optimized CSS */
        @media print {
            .page-break { page-break-before: always; }
            .chart-container { page-break-inside: avoid; }
        }
        .report-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .kpi-card { border-left: 4px solid #3B82F6; }
        .trend-up { color: #10B981; }
        .trend-down { color: #EF4444; }
    </style>
</head>
<body>
    {{-- Report sections --}}
    @include('reports.sections.header')
    @include('reports.sections.headline')
    @include('reports.sections.kpis')
    @include('reports.sections.concentration')
    @include('reports.sections.segments')
    @include('reports.sections.movers')
    @include('reports.sections.churn')
    @include('reports.sections.cohorts')
    @include('reports.sections.actions')
    
    {{-- Charts (Chart.js) --}}
    <script>
        // Chart.js configurations for PDF rendering
        const charts = {
            rfmDistribution: new Chart(document.getElementById('rfm-distribution'), {
                type: 'bar',
                data: @json($chartData['rfmDistribution']),
                options: { responsive: true, maintainAspectRatio: false }
            }),
            revenueTrend: new Chart(document.getElementById('revenue-trend'), {
                type: 'line',
                data: @json($chartData['revenueTrend']),
                options: { responsive: true, maintainAspectRatio: false }
            }),
            // ... more charts
        };
    </script>
</body>
</html>
```

---

## 5) Phased Delivery

**Phase 1 — Deterministic Core (No AI)**  
- Implement `RfmTools` (Enhanced KPIs + diagnostics with flexible comparison)
- Build `ComparisonPeriodResolver` and `RfmConfigurationManager`
- Build Blade report (HTML) + Browsershot PDF with Chart.js
- Add comparison period and RFM window selection UI
- Add PHPUnit golden tests for KPI JSON
- Result: shippable, zero AI dependency/cost

**Phase 2 — Narrative Seam (Still No AI)**  
- Create `NarrativeWriter` interface + `DeterministicNarrativeWriter`
- Wire into controller → report uses pluggable narrative
- Add comparison period and RFM window context to narratives
- Add configuration management UI

**Phase 3 — OpenAI Narrative (Explanations)**  
- Add `OpenAiNarrativeWriter` (OpenAI GPT-4 via Guzzle)
- Strict prompt: "Use only supplied JSON; do not invent numbers; explain using diagnostics, comparison period, and RFM window context."
- Optional numeric validation pass (replace mismatched sentences with deterministic text)
- Config‑switch in `.env`: `NARRATIVE_DRIVER=deterministic|openai|ollama`

**Phase 4 — Agent/Q&A (Optional)**  
- Chat box for follow‑ups (e.g., "Which five clients drove the decline vs Q1 with 10-month window?")
- Agent calls the same deterministic tools; never recomputes numbers itself
- Support for comparison period and RFM window queries
- Context-aware responses based on user's configuration

---

## 6) OpenAI Integration (Phase 3+)

### Configuration
```env
# .env
OPENAI_API_KEY=your_openai_api_key
OPENAI_MODEL=gpt-4-turbo-preview
NARRATIVE_DRIVER=openai
OPENAI_MAX_TOKENS=2000
OPENAI_TEMPERATURE=0.3
```

### Enhanced Prompt Engineering
```php
class OpenAiNarrativeWriter implements NarrativeWriter
{
    private function buildPrompt(array $payload): string
    {
        return "
You are a senior commercial analyst specializing in RFM (Recency, Frequency, Monetary) analysis.

CONTEXT:
- Report Date: {$payload['as_of']}
- Comparison Period: {$payload['comparison_period']}
- RFM Window: {$payload['rfm_window_months']} months
- Previous Period: {$payload['comparison_as_of']}

RULES:
1. Use ONLY the provided JSON data - do not invent numbers or causes
2. If a cause isn't evidenced, say 'not enough data'
3. Consider the RFM window context in your analysis
4. Explain changes relative to the comparison period
5. Use business-friendly language
6. Percentages with 1 decimal place; currency as whole numbers

REQUIRED SECTIONS:
1. Headline Summary
2. Revenue & AOV Analysis (attribute changes to volume vs AOV vs mix)
3. Customer Concentration Analysis
4. Customer Movement (New/Returned/Lost)
5. Top Movers (Improvers/Decliners)
6. Key Drivers & Insights
7. Recommended Actions

JSON DATA:
" . json_encode($payload, JSON_PRETTY_PRINT);
    }
}
```

---

## 7) Configuration & Security

**Config**
```env
# .env
REPORTS_PDF_DRIVER=browsershot
NARRATIVE_DRIVER=deterministic
OPENAI_API_KEY=your_openai_api_key
OPENAI_MODEL=gpt-4-turbo-preview
NODE_BINARY=/usr/bin/node
NPM_BINARY=/usr/bin/npm
CHROME_PATH=/usr/bin/google-chrome
```

**Security**
- Endpoints behind `auth`; scope by `user_id`
- OpenAI API calls: redact client names, use pseudonyms for PII
- Rate limiting on OpenAI API calls
- Input validation for all comparison periods and RFM windows

**Auditability**
- Store: snapshot date, comparison period, RFM window, payload hashes, model name/version, prompt (or hash), final narrative text, and tool outputs
- Log all OpenAI API calls with costs and response quality metrics

---

## 8) Acceptance Criteria

- KPIs match SQL aggregates for the chosen snapshot and comparison period
- Top‑10/Top‑50 shares and "customers to 80%" match deterministic calculations
- New/Returned/Lost/Dormant classifications are consistent with comparison period rules
- PDF renders in ≤ 10s; HTML in ≤ 1s (typical data volumes)
- Charts render correctly in PDF via Browsershot
- Comparison periods work correctly (monthly, quarterly, yearly, custom)
- RFM windows work correctly (6, 10, 12, 18, 24 months)
- With deterministic driver, narrative contains no LLM‑specific phrasing
- With OpenAI driver, any numeric references match KPI JSON (or are auto‑corrected)
- User configurations are saved and applied correctly

---

## 9) Enhanced Example Narrative Payload

```json
{
  "as_of": "2024-05-31",
  "comparison_period": "quarterly",
  "comparison_as_of": "2024-02-29",
  "rfm_window_months": 10,
  "kpis": {
    "revenue_curr": 553000,
    "revenue_prev": 594300,
    "aov_curr": 1870,
    "aov_prev": 1994,
    "avg_rfm_all": 3.4,
    "avg_rfm_top50": 5.2,
    "top10_share": 0.43,
    "top50_share": 0.93,
    "customers_to_80pct": 25,
    "active_customers": 105,
    "new_count": 9,
    "returned_count": 7,
    "lost_count": 26,
    "dormant_6m_count": 31,
    "improved_count": 17,
    "declined_count": 72,
    "clv_curr": 5267,
    "clv_prev": 4953,
    "churn_rate": 24.8,
    "retention_rate": 75.2,
    "gini_coefficient": 0.67,
    "avg_order_frequency": 2.3,
    "revenue_per_customer": 5267
  },
  "segments": {
    "high_value": {"count": 15, "revenue_share": 0.45},
    "mid_value": {"count": 35, "revenue_share": 0.38},
    "low_value": {"count": 40, "revenue_share": 0.15},
    "at_risk": {"count": 15, "revenue_share": 0.02}
  },
  "revenue_breakdown": {
    "volume_effect": -12000,
    "aov_effect": -21000,
    "mix_effect": -8000
  },
  "recency": { "recent_share": 0.34, "rfm_up_revenue_down": true },
  "concentration": {
    "top10_share_prev": 0.45, "top10_share_curr": 0.43,
    "customers_to_80pct_prev": 29, "customers_to_80pct_curr": 25
  },
  "movers": { "improvers": [], "decliners": [] },
  "churn": { "lost": [], "dormant": [] },
  "cohorts": {
    "new_customers": {"count": 9, "avg_rfm": 4.2},
    "returning_customers": {"count": 7, "avg_rfm": 5.1}
  },
  "findings": [
    {"tag":"RECENCY_SPIKE","severity":"info","evidence":{"recent_share":0.34}},
    {"tag":"AOV_PRESSURE","severity":"warn","evidence":{"aov_change_pct":-0.06}},
    {"tag":"CONCENTRATION_UP","severity":"warn","evidence":{"customers_to_80pct_curr":25,"customers_to_80pct_prev":29}},
    {"tag":"CLV_IMPROVEMENT","severity":"positive","evidence":{"clv_change_pct":0.06}}
  ]
}
```

---

## 10) Enhanced Prompt Skeleton (OpenAI)

**System:**  
"You are a senior commercial analyst specializing in RFM (Recency, Frequency, Monetary) analysis. Use **only** the provided JSON/tool outputs. Do **not** invent numbers or causes. If a cause isn't evidenced, say 'not enough data'. Consider the RFM window context ({{rfm_window_months}} months) and comparison period ({{comparison_period}}). Output sections: Headline; Revenue & AOV (attribute Δ to volume vs AOV vs mix); Concentration; Customer Movement; Top Movers; Key Drivers; Actions. Percentages with 1 decimal; £ as whole numbers. Be business-focused and actionable."

**User:**  
"Write the RFM report for {{date}} comparing to the {{comparison_period}} period using a {{rfm_window_months}}-month RFM window. Here is the JSON payload: …"

---

## 11) Edge Cases & Performance

- **Empty snapshot:** return a helpful message (no data in window)
- **Zero transactions:** AOV guard (`NULLIF(SUM(txn_count),0)`)
- **No comparison snapshot:** render "baseline" view with N/A deltas
- **Browsershot setup:** ensure Chrome/Chromium is installed on server
- **Data volume:** add the indexes above; paginate Top‑N lists if needed
- **Comparison period edge cases:** handle month-end vs quarter-end date logic
- **RFM window edge cases:** handle periods with insufficient data
- **OpenAI rate limits:** implement exponential backoff and fallback to deterministic
- **Chart rendering:** ensure Chart.js renders correctly in headless Chrome

---

## 12) Complete File Map

```
app/Services/RfmTools.php
app/Services/ComparisonPeriodResolver.php
app/Services/RfmConfigurationManager.php
app/Contracts/NarrativeWriter.php
app/Services/Narrative/DeterministicNarrativeWriter.php
app/Services/Narrative/OpenAiNarrativeWriter.php
app/Services/Narrative/LlmNarrativeWriterOllama.php
app/Http/Controllers/RfmReportController.php
app/Http/Controllers/RfmAgentController.php
app/Http/Controllers/RfmConfigurationController.php
app/Models/RfmConfiguration.php
config/reports.php
database/migrations/xxxx_add_rfm_window_to_rfm_reports.php
database/migrations/xxxx_create_rfm_configurations_table.php
resources/views/reports/rfm.blade.php
resources/views/reports/sections/
  ├── header.blade.php
  ├── headline.blade.php
  ├── kpis.blade.php
  ├── concentration.blade.php
  ├── segments.blade.php
  ├── movers.blade.php
  ├── churn.blade.php
  ├── cohorts.blade.php
  └── actions.blade.php
resources/views/rfm/config.blade.php
resources/views/reports/_narrative-deterministic.blade.php
tests/Feature/RfmReportTest.php
tests/Feature/RfmConfigurationTest.php
```

---

## 13) Implementation Checklist

### Phase 1: Core Infrastructure
- [ ] Add `rfm_window_months` field to `rfm_reports` table
- [ ] Create `rfm_configurations` table
- [ ] Implement `RfmConfigurationManager`
- [ ] Implement `ComparisonPeriodResolver`
- [ ] Enhance `RfmTools` with new KPIs and flexible comparison
- [ ] Add recommended indexes
- [ ] Create RFM configuration UI

### Phase 2: Report Generation
- [ ] Build enhanced Blade report templates
- [ ] Implement Browsershot PDF generation
- [ ] Add Chart.js integration for visualizations
- [ ] Create `DeterministicNarrativeWriter`
- [ ] Add comparison period and RFM window selectors
- [ ] Implement report caching for performance

### Phase 3: OpenAI Integration
- [ ] Add OpenAI API configuration
- [ ] Implement `OpenAiNarrativeWriter`
- [ ] Add prompt engineering and validation
- [ ] Implement rate limiting and error handling
- [ ] Add narrative quality metrics
- [ ] Create fallback mechanisms

### Phase 4: Advanced Features
- [ ] Implement Agent/Q&A system
- [ ] Add conversation context management
- [ ] Create advanced chart visualizations
- [ ] Implement report scheduling
- [ ] Add export options (CSV, Excel)
- [ ] Performance optimization

### Testing & Quality
- [ ] PHPUnit tests for all services
- [ ] Feature tests for report generation
- [ ] Integration tests for OpenAI API
- [ ] Performance benchmarks
- [ ] Security audit
- [ ] User acceptance testing

---

## 14) Performance Considerations

### Database Optimization
- Composite indexes for common query patterns
- Query result caching for expensive calculations
- Pagination for large datasets

### PDF Generation
- Async PDF generation for large reports
- PDF caching to avoid regeneration
- Optimized Chart.js rendering for headless Chrome

### OpenAI API
- Request batching where possible
- Response caching for similar queries
- Fallback to deterministic narratives on API failures

### Memory Management
- Streaming for large data exports
- Garbage collection for chart rendering
- Memory limits for PDF generation

---

## 15) Monitoring & Analytics

### Key Metrics to Track
- Report generation time (HTML vs PDF)
- OpenAI API response times and costs
- User engagement with different comparison periods
- Most popular RFM window configurations
- Chart rendering success rates
- PDF generation success rates

### Error Tracking
- OpenAI API failures and fallbacks
- Browsershot rendering errors
- Database query performance
- Memory usage during report generation

---

This comprehensive plan provides a complete roadmap for implementing advanced RFM reporting with flexible configuration, enhanced analytics, and OpenAI-powered insights, all while maintaining the deterministic foundation for reliability.
