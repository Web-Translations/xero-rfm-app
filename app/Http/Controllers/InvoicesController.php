<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\XeroInvoice;
use App\Models\ExcludedInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Webfox\Xero\OauthCredentialManager;
use XeroAPI\XeroPHP\Api\AccountingApi;

class InvoicesController extends Controller
{
    /**
     * List + filter invoices (index page)
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $days     = (int) $request->get('days', 0);       // 0 = show all
        $statuses = (array) $request->get('statuses', []);
        $q        = trim((string) $request->get('q', ''));

        // Get active connection
        $activeConnection = $user->getActiveXeroConnection();
        if (! $activeConnection) {
            return redirect()->route('dashboard')->withErrors('Please connect a Xero organisation first.');
        }

        $query = XeroInvoice::query()
            ->where('user_id', $user->id)
            ->where('tenant_id', $activeConnection->tenant_id)
            ->orderByDesc('date');

        // Only apply date filter if user specifically requests it
        if ($days > 0) {
            $fromDate = Carbon::now()->subDays($days)->toDateString();
            $query->where('date', '>=', $fromDate);
        }

        // Removed type filter since all invoices are ACCREC (sales invoices)

        if (! empty($statuses)) {
            $query->whereIn('status', $statuses);
        }

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('invoice_number', 'like', '%' . $q . '%')
                    ->orWhereIn('contact_id', function ($sub) use ($q) {
                        $sub->select('contact_id')
                            ->from('clients')
                            ->where('name', 'like', '%' . $q . '%');
                    });
            });
        }

        $invoices = $query->paginate(15)->withQueryString();

        // Get total counts for user feedback
        $totalInvoices = XeroInvoice::where('user_id', $user->id)
            ->where('tenant_id', $activeConnection->tenant_id)
            ->count();
        $filteredCount = $invoices->total();

        // Preload client names
        $contactIds = $invoices->pluck('contact_id')->unique()->filter()->values();
        $clients = Client::query()
            ->where('user_id', $user->id)
            ->where('tenant_id', $activeConnection->tenant_id)
            ->whereIn('contact_id', $contactIds)
            ->get()
            ->keyBy('contact_id');

        // Get excluded invoice IDs for the current user/tenant
        $excludedInvoiceIds = ExcludedInvoice::getExcludedInvoiceIds($user->id, $activeConnection->tenant_id);

        return view('invoices.index', [
            'invoices'           => $invoices,
            'clients'            => $clients,
            'days'               => $days,
            'statuses'           => $statuses,
            'q'                  => $q,
            'totalInvoices'      => $totalInvoices,
            'filteredCount'      => $filteredCount,
            'excludedInvoiceIds' => $excludedInvoiceIds,
            'lastSyncInfo' => [
                'last_sync_at' => $activeConnection->last_sync_at,
                'last_sync_invoice_count' => $activeConnection->last_sync_invoice_count,
            ],
        ]);
    }

    /**
     * Get invoice RFM timeline data for charts (monthly by default).
     * Returns compact rows so the chart renders even with many invoices.
     *
     * Query params:
     *   - client_id (optional)
     *   - months_back (int, default 12)
     *   - group_by ("month" | "date", default "month")
     */
    public function getRfmTimeline(Request $request)
    {
        $user = $request->user();

        // Get active connection
        $activeConnection = $user->getActiveXeroConnection();
        if (! $activeConnection) {
            return response()->json(['error' => 'No active Xero connection'], 400);
        }

        $clientId   = $request->get('client_id'); // Optional: filter by specific client
        $monthsBack = max(1, (int) $request->get('months_back', 12));
        $groupBy    = $request->get('group_by', 'month'); // "month" (default) or "date"

        $dateCutoff = now()->subMonths($monthsBack)->startOfDay();

        // Choose DB-specific trunc expression
        [$periodExpr, $periodAlias] = $this->periodTruncExpression($groupBy === 'date' ? 'date' : 'month');

        $base = XeroInvoice::query()
            ->join('clients', 'clients.contact_id', '=', 'xero_invoices.contact_id')
            ->where('xero_invoices.user_id', $user->id)
            ->where('xero_invoices.tenant_id', $activeConnection->tenant_id)
            ->where('xero_invoices.date', '>=', $dateCutoff)
            ->whereNotNull('xero_invoices.rfm_score');

        if ($clientId) {
            $base->where('clients.id', $clientId);
        }

        // Apply invoice exclusions for consistency across the app
        $excludedInvoiceIds = ExcludedInvoice::getExcludedInvoiceIds($user->id, $activeConnection->tenant_id);
        if (!empty($excludedInvoiceIds)) {
            $base->whereNotIn('xero_invoices.invoice_id', $excludedInvoiceIds);
        }

        // Aggregate in SQL so the payload is chart-ready
        $rows = $base->selectRaw("
                {$periodExpr} as {$periodAlias},
                AVG(xero_invoices.rfm_score) as avg_rfm_score,
                AVG(xero_invoices.r_score)  as avg_r_score,
                AVG(xero_invoices.f_score)  as avg_f_score,
                AVG(xero_invoices.m_score)  as avg_m_score,
                SUM(xero_invoices.total)    as total_revenue,
                COUNT(*)                    as invoice_count
            ")
            ->groupBy($periodAlias)
            ->orderBy($periodAlias, 'asc')
            ->get();

        return response()->json([
            'timeline_data' => $rows,
            'total_invoices' => (int) $rows->sum('invoice_count'),
            'date_range' => [
                'start' => optional($rows->first())?->{$periodAlias},
                'end'   => optional($rows->last())?->{$periodAlias},
            ],
        ]);
    }

    /**
     * Get simple RFM data for charts - just R, F, M scores, date, and client ID
     * Uses RfmReport; if it has no rows, returns an empty array (front-end should handle this).
     */
    public function getRfmData(Request $request)
    {
        $user = $request->user();

        // Get active connection
        $activeConnection = $user->getActiveXeroConnection();
        if (! $activeConnection) {
            return response()->json(['error' => 'No active Xero connection'], 400);
        }

        $clientId   = $request->get('client_id'); // Optional
        $monthsBack = max(1, (int) $request->get('months_back', 12));
        $dateCutoff = now()->subMonths($monthsBack)->startOfDay();

        $query = \App\Models\RfmReport::select([
                'rfm_reports.snapshot_date as date',
                'rfm_reports.r_score',
                'rfm_reports.f_score',
                'rfm_reports.m_score',
                'rfm_reports.rfm_score',
                'rfm_reports.client_id',
                'clients.name as client_name',
            ])
            ->join('clients', 'clients.id', '=', 'rfm_reports.client_id')
            ->where('rfm_reports.user_id', $user->id)
            ->where('clients.tenant_id', $activeConnection->tenant_id)
            ->where('rfm_reports.snapshot_date', '>=', $dateCutoff)
            ->where('rfm_reports.rfm_score', '>', 0)
            ->orderBy('rfm_reports.snapshot_date', 'asc');

        if ($clientId) {
            $query->where('rfm_reports.client_id', $clientId);
        }

        $rfmData = $query->get();

        return response()->json([
            'rfm_data' => $rfmData,
            'total_records' => $rfmData->count(),
            'date_range' => [
                'start' => optional($rfmData->first())->date,
                'end'   => optional($rfmData->last())->date,
            ],
            'clients' => $rfmData->pluck('client_name')->unique()->values(),
        ]);
    }

    /**
     * Sync Xero invoices (ACCREC) and compute per-invoice RFM scores.
     */
    public function sync(Request $request, OauthCredentialManager $xero)
    {
        $user = $request->user();

        // Ensure user has an active connection
        $activeConnection = $user->getActiveXeroConnection();
        if (! $activeConnection) {
            return redirect()->route('invoices.index')->withErrors('Connect Xero first.');
        }

        // Check if this is an AJAX request for progress updates
        if ($request->ajax()) {
            if ($request->has('action')) {
                return $this->handleSyncProgress($request, $user, $activeConnection);
            } else {
                // Initial sync request
                return $this->startSync($user, $activeConnection);
            }
        }

        // Non-AJAX request - redirect with error
        return redirect()->route('invoices.index')->withErrors('Please use the sync button to start the sync process.');
    }

    private function startSync($user, $activeConnection)
    {
        // Store sync session data
        session([
            'sync_user_id' => $user->id,
            'sync_tenant_id' => $activeConnection->tenant_id,
            'sync_total_pages' => 0,
            'sync_current_page' => 0,
            'sync_processed_invoices' => 0,
            'sync_status' => 'starting'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sync started',
            'sync_id' => uniqid()
        ]);
    }

    private function handleSyncProgress(Request $request, $user, $activeConnection)
    {
        $action = $request->get('action');
        
        switch ($action) {
            case 'get_progress':
                return $this->getSyncProgress();
            case 'fetch_batch':
                return $this->fetchAndProcessBatch($user, $activeConnection);
            case 'complete':
                return $this->completeSync($user, $activeConnection);
            default:
                return response()->json(['error' => 'Invalid action'], 400);
        }
    }

    private function getSyncProgress()
    {
        $progress = [
            'status' => session('sync_status', 'idle'),
            'processed_invoices' => session('sync_processed_invoices', 0),
        ];

        return response()->json($progress);
    }

    private function fetchAndProcessBatch($user, $activeConnection)
    {
        /** @var AccountingApi $api */
        $api = app(AccountingApi::class);
        $tenantId = $activeConnection->tenant_id;

        // Fetch sales invoices from Xero (all invoices are ACCREC)
        $where = 'Type=="ACCREC"';
        $currentPage = session('sync_current_page', 0) + 1;

        try {
            $resp = $api->getInvoices(
                $tenantId,
                null,
                $where,
                'Date DESC',
                null,
                null,
                null,
                null,
                $currentPage,
                null,
                null,
                null,
                null,
                null,
                null
            );
            

            
            $batch = $resp?->getInvoices() ?? [];
            $batchSize = count($batch);

            if ($batchSize > 0) {
                // Process this batch
                $this->processBatch($batch, $user, $tenantId);
                
                // Update session with progress
                $processedInvoices = session('sync_processed_invoices', 0) + $batchSize;
                session([
                    'sync_current_page' => $currentPage,
                    'sync_processed_invoices' => $processedInvoices,
                    'sync_status' => 'processing'
                ]);

                // Simple progress tracking - just count invoices processed
                // No complex estimation needed

                return response()->json([
                    'success' => true,
                    'batch_size' => $batchSize,
                    'current_page' => $currentPage,
                    'processed_invoices' => $processedInvoices,
                    'has_more' => $batchSize === 100
                ]);
            } else {
                // No more invoices to fetch
                return response()->json([
                    'success' => true,
                    'batch_size' => 0,
                    'current_page' => $currentPage,
                    'has_more' => false,
                    'completed' => true
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch batch: ' . $e->getMessage()
            ], 500);
        }
    }

    private function processBatch($batch, $user, $tenantId)
    {
        DB::transaction(function () use ($batch, $user, $tenantId) {
            foreach ($batch as $inv) {
                $contact = $inv->getContact();

                if ($contact) {
                    Client::updateOrCreate(
                        ['user_id' => $user->id, 'contact_id' => $contact->getContactId()],
                        [
                            'tenant_id' => $tenantId,
                            'name'      => $contact->getName(),
                        ]
                    );
                }

                XeroInvoice::updateOrCreate(
                    ['user_id' => $user->id, 'invoice_id' => $inv->getInvoiceId()],
                    [
                        'tenant_id'         => $tenantId,
                        'contact_id'        => optional($contact)->getContactId(),
                        'status'            => $inv->getStatus(),
                        // Removed type field since all invoices are ACCREC
                        'invoice_number'    => $inv->getInvoiceNumber(),
                        'date'              => $this->extractInvoiceDate($inv),
                        'due_date'          => $this->formatDateField($inv->getDueDate()),
                        'subtotal'          => $inv->getSubTotal(),
                        'total'             => $inv->getTotal(),
                        'currency'          => $inv->getCurrencyCode(),
                        'updated_date_utc'  => $this->formatDateTimeField($inv->getUpdatedDateUtc()),
                        'fully_paid_at'     => $this->formatDateTimeField($inv->getFullyPaidOnDate()),
                    ]
                );
            }
        });
    }

    private function completeSync($user, $activeConnection)
    {
        $processedInvoices = session('sync_processed_invoices', 0);

        // Store last sync information in the database
        $this->updateLastSyncInfo($user, $activeConnection, $processedInvoices);

        // Clear sync session data
        session()->forget([
            'sync_user_id',
            'sync_tenant_id',
            'sync_total_pages',
            'sync_current_page',
            'sync_processed_invoices',
            'sync_status'
        ]);

        return response()->json([
            'success' => true,
            'message' => "Successfully synced {$processedInvoices} invoices",
            'processed_invoices' => $processedInvoices
        ]);
    }

    private function updateLastSyncInfo($user, $activeConnection, $processedInvoices)
    {
        // Update the XeroConnection with last sync info
        $activeConnection->update([
            'last_sync_at' => now(),
            'last_sync_invoice_count' => $processedInvoices
        ]);
    }

    /**
     * Calculate RFM scores for each individual invoice
     */
    private function calculateInvoiceRfmScores($userId, $tenantId): void
    {
        // Get all invoices for this user/tenant, ordered by date
        $invoices = XeroInvoice::where('user_id', $userId)
            ->where('tenant_id', $tenantId)
            ->orderBy('date', 'asc')
            ->get();

        if ($invoices->isEmpty()) {
            return;
        }

        // Group invoices by contact_id for processing
        $invoicesByContact = $invoices->groupBy('contact_id');

        foreach ($invoicesByContact as $contactInvoices) {
            // Ensure collection is sorted by date
            $contactInvoices = $contactInvoices->sortBy('date')->values();

            foreach ($contactInvoices as $index => $invoice) {
                /** @var \App\Models\XeroInvoice $invoice */
                $invoiceDate = Carbon::parse($invoice->date);

                // Calculate R Score: 10 - months since last transaction (minimum 0)
                $rScore = $this->calculateRScore($contactInvoices, $index, $invoiceDate);

                // Calculate F Score: number of invoices in past 12 months (capped at 10)
                $fScore = $this->calculateFScore($contactInvoices, $index, $invoiceDate);

                // Calculate M Score: normalized monetary value (0-10 scale)
                $mScore = $this->calculateMScore($contactInvoices, $index, $invoiceDate);

                // Calculate overall RFM score: (R + F + M) / 3
                $rfmScore = round(($rScore + $fScore + $mScore) / 3, 2);

                // Update the invoice with RFM scores
                $invoice->update([
                    'r_score'           => $rScore,
                    'f_score'           => $fScore,
                    'm_score'           => $mScore,
                    'rfm_score'         => $rfmScore,
                    'rfm_calculated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Calculate R Score for a specific invoice
     */
    private function calculateRScore($contactInvoices, $currentIndex, Carbon $invoiceDate): int
    {
        // Find the previous invoice (by date)
        $previous = null;
        for ($i = $currentIndex - 1; $i >= 0; $i--) {
            if (Carbon::parse($contactInvoices[$i]->date)->lt($invoiceDate)) {
                $previous = $contactInvoices[$i];
                break;
            }
        }

        if (! $previous) {
            // First invoice for this contact
            return 10;
        }

        // Months since last transaction
        $monthsSinceLast = $invoiceDate->diffInMonths(Carbon::parse($previous->date));
        return max(0, 10 - $monthsSinceLast);
    }

    /**
     * Calculate F Score for a specific invoice
     */
    private function calculateFScore($contactInvoices, $currentIndex, Carbon $invoiceDate): int
    {
        $twelveMonthsAgo = (clone $invoiceDate)->subMonths(12)->startOfDay();
        $count = 0;

        // Count invoices in the past 12 months up to this invoice
        for ($i = 0; $i <= $currentIndex; $i++) {
            $d = Carbon::parse($contactInvoices[$i]->date);
            if ($d->gte($twelveMonthsAgo) && $d->lte($invoiceDate)) {
                $count++;
            }
        }

        return min(10, $count);
    }

    /**
     * Calculate M Score for a specific invoice
     */
    private function calculateMScore($contactInvoices, $currentIndex, Carbon $invoiceDate): float
    {
        $twelveMonthsAgo = (clone $invoiceDate)->subMonths(12)->startOfDay();
        $monetaryValues = [];

        // Get all monetary values in the past 12 months up to this invoice
        for ($i = 0; $i <= $currentIndex; $i++) {
            $rowDate = Carbon::parse($contactInvoices[$i]->date);
            if ($rowDate->gte($twelveMonthsAgo) && $rowDate->lte($invoiceDate)) {
                $monetaryValues[] = (float) $contactInvoices[$i]->total;
            }
        }

        if (empty($monetaryValues)) {
            return 0.0;
        }

        // Min-max scaling to 0-10 scale
        $min = min($monetaryValues);
        $max = max($monetaryValues);

        if ($max <= $min) {
            return 0.0;
        }

        $currentTotal = (float) $contactInvoices[$currentIndex]->total;
        return round((($currentTotal - $min) / ($max - $min)) * 10, 2);
    }

    /**
     * Extract a normalized Y-m-d invoice date from Xero SDK value
     */
    private function extractInvoiceDate($inv): ?string
    {
        // Helper function to safely format dates
        $formatDate = function ($dateValue) {
            if (! $dateValue) {
                return null;
            }

            // Handle .NET JSON date format: /Date(timestamp+offset)/
            if (is_string($dateValue) && preg_match('/\/Date\((\d+)([+-]\d+)?\)\//', $dateValue, $m)) {
                $timestamp = (int) ($m[1] / 1000); // ms -> s
                return date('Y-m-d', $timestamp);
            }

            if (is_string($dateValue)) {
                // Already a string; trust first 10 chars if looks like datetime
                return substr($dateValue, 0, 10);
            }

            if (method_exists($dateValue, 'format')) {
                return $dateValue->format('Y-m-d');
            }

            return null;
        };

        // Prefer actual invoice date
        $date = $formatDate($inv->getDate());
        if ($date) {
            return $date;
        }

        // Try to find a date in line items (sometimes appears there)
        $lineItems = $inv->getLineItems();
        if ($lineItems && count($lineItems) > 0) {
            foreach ($lineItems as $lineItem) {
                $description = $lineItem->getDescription();
                if ($description && preg_match('/(\d{4}-\d{2}-\d{2})/', $description, $m)) {
                    return $m[1];
                }
            }
        }

        // Fallback to due date
        $dueDate = $formatDate($inv->getDueDate());
        if ($dueDate) {
            return $dueDate;
        }

        // Fallback to updated date
        $updatedDate = $inv->getUpdatedDateUtc();
        if ($updatedDate) {
            if (is_string($updatedDate) && preg_match('/\/Date\((\d+)([+-]\d+)?\)\//', $updatedDate, $m)) {
                $timestamp = (int) ($m[1] / 1000);
                return date('Y-m-d', $timestamp);
            }
            if (is_string($updatedDate)) {
                return substr($updatedDate, 0, 10);
            }
            if (method_exists($updatedDate, 'format')) {
                return $updatedDate->format('Y-m-d');
            }
        }

        // Last resort: today (avoids nulls)
        return now()->format('Y-m-d');
    }

    private function formatDateField($dateValue): ?string
    {
        return $this->formatDateTimeField($dateValue);
    }

    private function formatDateTimeField($dateValue): ?string
    {
        if (! $dateValue) {
            return null;
        }

        if (is_string($dateValue)) {
            // Handle .NET JSON date format: /Date(timestamp+offset)/
            if (preg_match('/\/Date\((\d+)([+-]\d+)?\)\//', $dateValue, $m)) {
                $timestamp = (int) ($m[1] / 1000); // ms -> s
                return date('Y-m-d H:i:s', $timestamp);
            }
            // Assume standard ISO string
            $s = substr($dateValue, 0, 19);
            // If only date provided
            if (strlen($s) === 10) {
                return $s . ' 00:00:00';
            }
            return $s;
        }

        if (method_exists($dateValue, 'format')) {
            return $dateValue->format('Y-m-d H:i:s');
        }

        return null;
    }

    /**
     * Exclude an invoice from RFM calculations
     */
    public function exclude(Request $request, string $invoiceId)
    {
        $user = $request->user();
        $activeConnection = $user->getActiveXeroConnection();

        if (! $activeConnection) {
            return response()->json(['error' => 'No active organisation'], 400);
        }

        // Verify the invoice exists and belongs to the user
        $invoice = XeroInvoice::where('user_id', $user->id)
            ->where('tenant_id', $activeConnection->tenant_id)
            ->where('invoice_id', $invoiceId)
            ->first();

        if (! $invoice) {
            return response()->json(['error' => 'Invoice not found'], 404);
        }

        // Create the exclusion record
        ExcludedInvoice::updateOrCreate(
            [
                'user_id'   => $user->id,
                'tenant_id' => $activeConnection->tenant_id,
                'invoice_id'=> $invoiceId,
            ],
            [
                'user_id'   => $user->id,
                'tenant_id' => $activeConnection->tenant_id,
                'invoice_id'=> $invoiceId,
            ]
        );
        // Mark exclusions changed for this organisation
        $activeConnection->update(['exclusions_changed_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Remove an invoice from exclusion list
     */
    public function unexclude(Request $request, string $invoiceId)
    {
        $user = $request->user();
        $activeConnection = $user->getActiveXeroConnection();

        if (! $activeConnection) {
            return response()->json(['error' => 'No active organisation'], 400);
        }

        ExcludedInvoice::where('user_id', $user->id)
            ->where('tenant_id', $activeConnection->tenant_id)
            ->where('invoice_id', $invoiceId)
            ->delete();
        // Mark exclusions changed for this organisation
        $activeConnection->update(['exclusions_changed_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Bulk exclude invoices matching current filters
     */
    public function bulkExclude(Request $request)
    {
        $user = $request->user();
        $activeConnection = $user->getActiveXeroConnection();
        if (! $activeConnection) {
            return response()->json(['error' => 'No active organisation'], 400);
        }

        $days     = (int) $request->get('days', 0);
        $statuses = (array) $request->get('statuses', []);
        $q        = trim((string) $request->get('q', ''));

        $query = XeroInvoice::query()
            ->where('user_id', $user->id)
            ->where('tenant_id', $activeConnection->tenant_id);

        if ($days > 0) {
            $fromDate = Carbon::now()->subDays($days)->toDateString();
            $query->where('date', '>=', $fromDate);
        }
        if (! empty($statuses)) {
            $query->whereIn('status', $statuses);
        }
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('invoice_number', 'like', '%' . $q . '%')
                    ->orWhereIn('contact_id', function ($sub) use ($q) {
                        $sub->select('contact_id')
                            ->from('clients')
                            ->where('name', 'like', '%' . $q . '%');
                    });
            });
        }

        $invoiceIds = $query->pluck('invoice_id')->unique()->values();
        if ($invoiceIds->isEmpty()) {
            return response()->json(['success' => true, 'affected' => 0]);
        }

        // Insert ignore duplicates using upsert-like behavior
        $now = now();
        $rows = $invoiceIds->map(function ($id) use ($user, $activeConnection, $now) {
            return [
                'user_id' => $user->id,
                'tenant_id' => $activeConnection->tenant_id,
                'invoice_id' => $id,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->all();

        // Use DB::table for bulk insert with ignore on duplicates
        $affected = 0;
        try {
            $affected = DB::table('excluded_invoices')->upsert(
                $rows,
                ['user_id', 'tenant_id', 'invoice_id'], // unique key
                ['updated_at']
            );
        } catch (\Throwable $e) {
            Log::error('Bulk exclude failed: ' . $e->getMessage());
        }

        // Mark exclusions changed for this organisation
        $activeConnection->update(['exclusions_changed_at' => now()]);

        return response()->json(['success' => true, 'affected' => $affected]);
    }

    /**
     * Bulk un-exclude invoices matching current filters
     */
    public function bulkUnexclude(Request $request)
    {
        $user = $request->user();
        $activeConnection = $user->getActiveXeroConnection();
        if (! $activeConnection) {
            return response()->json(['error' => 'No active organisation'], 400);
        }

        $days     = (int) $request->get('days', 0);
        $statuses = (array) $request->get('statuses', []);
        $q        = trim((string) $request->get('q', ''));

        $query = XeroInvoice::query()
            ->where('user_id', $user->id)
            ->where('tenant_id', $activeConnection->tenant_id);

        if ($days > 0) {
            $fromDate = Carbon::now()->subDays($days)->toDateString();
            $query->where('date', '>=', $fromDate);
        }
        if (! empty($statuses)) {
            $query->whereIn('status', $statuses);
        }
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('invoice_number', 'like', '%' . $q . '%')
                    ->orWhereIn('contact_id', function ($sub) use ($q) {
                        $sub->select('contact_id')
                            ->from('clients')
                            ->where('name', 'like', '%' . $q . '%');
                    });
            });
        }

        $invoiceIds = $query->pluck('invoice_id')->unique()->values();
        if ($invoiceIds->isEmpty()) {
            return response()->json(['success' => true, 'affected' => 0]);
        }

        $affected = ExcludedInvoice::where('user_id', $user->id)
            ->where('tenant_id', $activeConnection->tenant_id)
            ->whereIn('invoice_id', $invoiceIds)
            ->delete();
        // Mark exclusions changed for this organisation
        $activeConnection->update(['exclusions_changed_at' => now()]);

        return response()->json(['success' => true, 'affected' => $affected]);
    }

    /**
     * Build a DB driver-specific expression to truncate a date to month or day.
     *
     * @param "month"|"date" $granularity
     * @return array{0:string,1:string} [expression, alias]
     */
    private function periodTruncExpression(string $granularity): array
    {
        $driver = DB::getDriverName();
        $alias  = $granularity === 'date' ? 'period_date' : 'period_start';

        if ($granularity === 'date') {
            // Exact date (YYYY-MM-DD)
            switch ($driver) {
                case 'pgsql':
                    return ["DATE(xero_invoices.date)", $alias];
                case 'sqlite':
                    return ["DATE(xero_invoices.date)", $alias];
                default: // mysql/mariadb
                    return ["DATE(xero_invoices.date)", $alias];
            }
        }

        // Month start (YYYY-MM-01)
        switch ($driver) {
            case 'pgsql':
                // date_trunc returns timestamp; cast to date
                return ["DATE(date_trunc('month', xero_invoices.date))", $alias];
            case 'sqlite':
                return ["DATE(strftime('%Y-%m-01', xero_invoices.date))", $alias];
            default: // mysql/mariadb
                return ["DATE_FORMAT(xero_invoices.date, '%Y-%m-01')", $alias];
        }
    }
}