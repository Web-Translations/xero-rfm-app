<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\XeroInvoice;
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

        return view('invoices.index', [
            'invoices' => $invoices,
            'clients' => $clients,
            'days' => $days,
            'statuses' => $statuses,
            'q' => $q,
            'totalInvoices' => $totalInvoices,
            'filteredCount' => $filteredCount,
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

        /** @var AccountingApi $api */
        $api = app(AccountingApi::class);
        $tenantId = $activeConnection->tenant_id;

        // Fetch sales invoices from Xero (ACCREC only)
        $where = 'Type=="ACCREC"';

        $page = 1; $all = [];
        do {
            $resp = $api->getInvoices(
                $tenantId,
                null,
                $where,
                'Date DESC',
                null,
                null,
                null,
                null,
                $page,
                null,
                null,
                null,
                null,
                null,
                null
            );
            $batch = $resp?->getInvoices() ?? [];
            $all = array_merge($all, $batch);
            $page++;
        } while (count($batch) === 100);

        // Count invoices for user feedback
        $invoiceCount = count($all);

        // Upsert Clients + Invoices
        DB::transaction(function () use ($user, $all, $tenantId) {
            foreach ($all as $inv) {
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

        $message = "Synced {$invoiceCount} sales invoices from Xero.";
        return redirect()->route('invoices.index')->with('status', $message);
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
}

