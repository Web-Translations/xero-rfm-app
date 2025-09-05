<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\RfmReport;
use App\Models\RfmConfiguration;
use App\Services\Rfm\RfmCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RfmController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get active connection
        $activeConnection = $user->getActiveXeroConnection();
        if (!$activeConnection) {
            return redirect()->route('dashboard')->withErrors('Please connect a Xero organisation first.');
        }
        
        $search = trim((string) $request->get('q', ''));
        $viewMode = $request->get('view', 'current'); // 'current' or a specific date

        // Get current RFM configuration
        $config = RfmConfiguration::getOrCreateDefault($user->id, $activeConnection->tenant_id);

        // Get RFM data based on view mode
        if ($viewMode === 'current') {
            // Get current RFM scores (today's date)
            $query = RfmReport::getCurrentScoresForUser($user->id, $activeConnection->tenant_id);
        } else {
            // Get historical snapshot for specific date (viewMode is the date)
            $query = RfmReport::getForSnapshotDate($user->id, $viewMode, $activeConnection->tenant_id);
        }

        // Apply search filter
        if ($search !== '') {
            $query->where('client_name', 'like', '%' . $search . '%');
        }

        // Filter out clients with RFM score of 0 (no point showing inactive clients)
        $query->where('rfm_score', '>', 0);

        $rows = $query->paginate(15)->withQueryString();

        // Get available snapshot dates for view mode dropdown
        $availableSnapshots = RfmReport::getAvailableSnapshotDates($user->id, $activeConnection->tenant_id);

        // Get total counts for user feedback
        $totalClients = Client::where('user_id', $user->id)
            ->where('tenant_id', $activeConnection->tenant_id)
            ->count();
        $filteredCount = $rows->total();

        // Determine if any invoices exist yet (controls guidance cards)
        $hasInvoices = \App\Models\XeroInvoice::where('user_id', $user->id)
            ->where('tenant_id', $activeConnection->tenant_id)
            ->exists();

        // Determine if recalculation is needed based on config/exclusions updates
        // Use the most recent compute timestamp across all reports for this tenant
        $lastComputedUpdatedAt = RfmReport::where('rfm_reports.user_id', $user->id)
            ->join('clients', 'clients.id', '=', 'rfm_reports.client_id')
            ->where('clients.tenant_id', $activeConnection->tenant_id)
            ->max('rfm_reports.updated_at');
        $lastComputedAt = $lastComputedUpdatedAt ? \Illuminate\Support\Carbon::parse($lastComputedUpdatedAt) : null;
        $exclusionsUpdatedAt = \App\Models\ExcludedInvoice::where('user_id', $user->id)
            ->where('tenant_id', $activeConnection->tenant_id)
            ->max('updated_at');
        $needsRecalc = false;
        if ($lastComputedAt) {
            if ($config->updated_at && \Illuminate\Support\Carbon::parse($config->updated_at)->gt($lastComputedAt)) {
                $needsRecalc = true;
            }
            if ($exclusionsUpdatedAt && \Illuminate\Support\Carbon::parse($exclusionsUpdatedAt)->gt($lastComputedAt)) {
                $needsRecalc = true;
            }
        }

        return view('rfm.index', [
            'rows' => $rows,
            'search' => $search,
            'viewMode' => $viewMode,
            'availableSnapshots' => $availableSnapshots,
            'totalClients' => $totalClients,
            'filteredCount' => $filteredCount,
            'currentDate' => now()->toDateString(),
            'config' => $config,
            'needsRecalc' => $needsRecalc,
            'hasInvoices' => $hasInvoices,
        ]);
    }

    public function sync(Request $request, RfmCalculator $calculator)
    {
        $user = $request->user();
        $action = $request->get('action', 'sync_all');

        // Get the active connection and configuration
        $activeConnection = $user->getActiveXeroConnection();
        if (!$activeConnection) {
            return redirect()->route('dashboard')->withErrors('Please connect a Xero organisation first.');
        }

        $config = RfmConfiguration::getOrCreateDefault($user->id, $activeConnection->tenant_id);

        if ($action === 'sync_all') {
            // Calculate current RFM scores with configuration
            $currentResult = $calculator->computeSnapshot($user->id, null, $config);
            
            // Calculate historical snapshots for all available data (36 months should cover most cases)
            $historicalResults = $calculator->computeHistoricalSnapshots($user->id, 36, $config);
            $totalHistorical = array_sum(array_column($historicalResults, 'computed'));
            
            // Clean up old snapshots that aren't on 1st of month
            $cleanedUp = $calculator->cleanupOldSnapshots($user->id);
            
            $status = "Synced RFM data: {$currentResult['computed']} current scores and {$totalHistorical} historical snapshots created. Cleaned up {$cleanedUp} old snapshots.";
        } else {
            // Fallback for old actions (if needed)
            if ($action === 'current') {
                $result = $calculator->computeSnapshot($user->id, null, $config);
                $status = "Calculated current RFM scores for {$result['computed']} clients.";
            } else {
                $monthsBack = (int) $request->get('months_back', 12);
                $results = $calculator->computeHistoricalSnapshots($user->id, $monthsBack, $config);
                $totalComputed = array_sum(array_column($results, 'computed'));
                $status = "Created historical snapshots for {$totalComputed} client records over {$monthsBack} months.";
            }
        }

        return redirect()->route('rfm.index')->with('status', $status);
    }
}

