<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\XeroInvoice;
use App\Models\ExcludedInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Webfox\Xero\OauthCredentialManager;
use XeroAPI\XeroPHP\Api\AccountingApi;

class InvoicesController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $days = (int) $request->get('days', 0); // 0 = show all
        $statuses = (array) $request->get('statuses', []);
        $q = trim((string) $request->get('q', ''));

        // Get active connection
        $activeConnection = $user->getActiveXeroConnection();
        if (!$activeConnection) {
            return redirect()->route('dashboard')->withErrors('Please connect a Xero organization first.');
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

        // Only show ACCREC by default (sales invoices)
        $query->where('type', 'ACCREC');

        if (!empty($statuses)) {
            $query->whereIn('status', $statuses);
        }

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('invoice_number', 'like', '%'.$q.'%')
                  ->orWhereIn('contact_id', function ($sub) use ($q) {
                      $sub->select('contact_id')
                          ->from('clients')
                          ->where('name', 'like', '%'.$q.'%');
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

        // Get excluded invoice IDs for the current page
        $excludedInvoiceIds = ExcludedInvoice::getExcludedInvoiceIds($user->id, $activeConnection->tenant_id);

        return view('invoices.index', [
            'invoices' => $invoices,
            'clients' => $clients,
            'days' => $days,
            'statuses' => $statuses,
            'q' => $q,
            'totalInvoices' => $totalInvoices,
            'filteredCount' => $filteredCount,
            'excludedInvoiceIds' => $excludedInvoiceIds,
            'lastSyncInfo' => [
                'last_sync_at' => $activeConnection->last_sync_at,
                'last_sync_invoice_count' => $activeConnection->last_sync_invoice_count,
            ],
        ]);
    }

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

        // Fetch sales invoices from Xero (ACCREC only)
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
            
            // Debug: Let's see what's in the response
            if ($currentPage === 1) {
                \Log::info('Xero API Response Debug', [
                    'response_class' => get_class($resp),
                    'response_methods' => get_class_methods($resp),
                    'has_getInvoices' => method_exists($resp, 'getInvoices'),
                    'has_getPagination' => method_exists($resp, 'getPagination'),
                    'has_getTotalCount' => method_exists($resp, 'getTotalCount'),
                    'has_getPage' => method_exists($resp, 'getPage'),
                    'has_getPageSize' => method_exists($resp, 'getPageSize'),
                    'has_getPageCount' => method_exists($resp, 'getPageCount'),
                ]);
            }
            
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
                            'name'      => $contact->getName()
                        ]
                    );
                }
                XeroInvoice::updateOrCreate(
                    ['user_id' => $user->id, 'invoice_id' => $inv->getInvoiceId()],
                    [
                        'tenant_id'         => $tenantId,
                        'contact_id'        => optional($contact)->getContactId(),
                        'status'            => $inv->getStatus(),
                        'type'              => $inv->getType(),
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

    private function extractInvoiceDate($inv)
    {
        // Helper function to safely format dates
        $formatDate = function($dateValue) {
            if (!$dateValue) return null;
            
            // Handle .NET JSON date format: /Date(timestamp+offset)/
            if (is_string($dateValue) && preg_match('/\/Date\((\d+)([+-]\d+)\)\//', $dateValue, $matches)) {
                $timestamp = (int)($matches[1] / 1000); // Convert milliseconds to seconds
                return date('Y-m-d', $timestamp);
            }
            
            if (is_string($dateValue)) return $dateValue;
            if (method_exists($dateValue, 'format')) {
                return $dateValue->format('Y-m-d');
            }
            return null;
        };
        

        
        // Try to get the actual invoice date first
        $date = $formatDate($inv->getDate());
        if ($date) {
            return $date;
        }
        
        // If no date, try to get from line items (sometimes the date is stored there)
        $lineItems = $inv->getLineItems();
        if ($lineItems && count($lineItems) > 0) {
            foreach ($lineItems as $lineItem) {
                if ($lineItem->getLineItemID()) {
                    // Sometimes the date is in the description or other fields
                    $description = $lineItem->getDescription();
                    if ($description && preg_match('/(\d{4}-\d{2}-\d{2})/', $description, $matches)) {
                        return $matches[1];
                    }
                }
            }
        }
        
        // Fallback to due date if available
        $dueDate = $formatDate($inv->getDueDate());
        if ($dueDate) {
            return $dueDate;
        }
        
        // Last resort: use updated date (but this might not be the actual invoice date)
        $updatedDate = $inv->getUpdatedDateUtc();
        if ($updatedDate) {
            if (is_string($updatedDate)) {
                // Handle .NET JSON date format for updated date too
                if (preg_match('/\/Date\((\d+)([+-]\d+)\)\//', $updatedDate, $matches)) {
                    $timestamp = (int)($matches[1] / 1000);
                    return date('Y-m-d', $timestamp);
                }
                return substr($updatedDate, 0, 10); // Extract Y-m-d from datetime string
            }
            if (method_exists($updatedDate, 'format')) {
                return $updatedDate->format('Y-m-d');
            }
        }
        
        // If all else fails, use today's date
        return now()->format('Y-m-d');
    }

    private function formatDateField($dateValue)
    {
        return $this->formatDateTimeField($dateValue);
    }

    private function formatDateTimeField($dateValue)
    {
        if (!$dateValue) return null;

        if (is_string($dateValue)) {
            // Handle .NET JSON date format: /Date(timestamp+offset)/
            if (preg_match('/\/Date\((\d+)([+-]\d+)\)\//', $dateValue, $matches)) {
                $timestamp = (int)($matches[1] / 1000); // Convert milliseconds to seconds
                return date('Y-m-d H:i:s', $timestamp);
            }
            return substr($dateValue, 0, 10); // Extract Y-m-d from datetime string
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
        
        if (!$activeConnection) {
            return response()->json(['error' => 'No active organization'], 400);
        }

        // Verify the invoice exists and belongs to the user
        $invoice = XeroInvoice::where('user_id', $user->id)
            ->where('tenant_id', $activeConnection->tenant_id)
            ->where('invoice_id', $invoiceId)
            ->first();

        if (!$invoice) {
            return response()->json(['error' => 'Invoice not found'], 404);
        }

        // Create the exclusion record
        ExcludedInvoice::updateOrCreate(
            [
                'user_id' => $user->id,
                'tenant_id' => $activeConnection->tenant_id,
                'invoice_id' => $invoiceId,
            ],
            [
                'user_id' => $user->id,
                'tenant_id' => $activeConnection->tenant_id,
                'invoice_id' => $invoiceId,
            ]
        );

        return response()->json(['success' => true]);
    }

    /**
     * Remove an invoice from exclusion list
     */
    public function unexclude(Request $request, string $invoiceId)
    {
        $user = $request->user();
        $activeConnection = $user->getActiveXeroConnection();
        
        if (!$activeConnection) {
            return response()->json(['error' => 'No active organization'], 400);
        }

        // Remove the exclusion record
        ExcludedInvoice::where('user_id', $user->id)
            ->where('tenant_id', $activeConnection->tenant_id)
            ->where('invoice_id', $invoiceId)
            ->delete();

        return response()->json(['success' => true]);
    }
}

