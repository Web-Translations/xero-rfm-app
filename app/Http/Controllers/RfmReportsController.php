<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\RfmReport;
use App\Models\RfmConfiguration;
use App\Models\XeroInvoice;
use App\Services\Rfm\RfmTools;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RfmReportsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get active connection
        $activeConnection = $user->getActiveXeroConnection();
        if (!$activeConnection) {
            return redirect()->route('dashboard')->withErrors('Please connect a Xero organisation first.');
        }

        // Get current RFM configuration
        $config = RfmConfiguration::getOrCreateDefault($user->id, $activeConnection->tenant_id);

        // Get available snapshot dates for comparison
        $availableSnapshots = RfmReport::getAvailableSnapshotDates($user->id, $activeConnection->tenant_id);

        return view('rfm.reports.index', [
            'activeConnection' => $activeConnection,
            'config' => $config,
            'availableSnapshots' => $availableSnapshots,
        ]);
    }

    public function generate(Request $request)
    {
        $user = $request->user();
        
        // Get active connection
        $activeConnection = $user->getActiveXeroConnection();
        if (!$activeConnection) {
            return redirect()->route('dashboard')->withErrors('Please connect a Xero organisation first.');
        }

        // Get parameters
        $snapshotDate = $request->get('snapshot_date', 'current');
        $comparisonPeriod = $request->get('comparison_period', 'monthly');
        $rfmWindow = $request->get('rfm_window', 12);

        // Get current RFM configuration
        $config = RfmConfiguration::getOrCreateDefault($user->id, $activeConnection->tenant_id);

        // Initialize RFM Tools service
        $rfmTools = new RfmTools();

        // Determine snapshot dates
        if ($snapshotDate === 'current') {
            $currentSnapshotDate = Carbon::now()->toDateString();
        } else {
            $currentSnapshotDate = $snapshotDate;
        }

        // Calculate comparison date based on period
        $comparisonSnapshotDate = $this->calculateComparisonDate($currentSnapshotDate, $comparisonPeriod);

        // Generate comprehensive KPIs
        $kpis = $rfmTools->computeKpis(
            $user->id,
            $activeConnection->tenant_id,
            $currentSnapshotDate,
            $comparisonSnapshotDate,
            $config
        );

        // Get RFM data for current period
        $currentRfmData = RfmReport::getForSnapshotDate($user->id, $currentSnapshotDate, $activeConnection->tenant_id)->get();
        
        // Get RFM data for comparison period (if available)
        $comparisonRfmData = collect();
        if ($comparisonSnapshotDate) {
            $comparisonRfmData = RfmReport::getForSnapshotDate($user->id, $comparisonSnapshotDate, $activeConnection->tenant_id)->get();
        }

        return view('rfm.reports.show', [
            'kpis' => $kpis,
            'currentRfmData' => $currentRfmData,
            'comparisonRfmData' => $comparisonRfmData,
            'currentSnapshotDate' => $currentSnapshotDate,
            'comparisonSnapshotDate' => $comparisonSnapshotDate,
            'comparisonPeriod' => $comparisonPeriod,
            'rfmWindow' => $rfmWindow,
            'config' => $config,
            'activeConnection' => $activeConnection,
        ]);
    }

    private function calculateComparisonDate(string $currentDate, string $period): ?string
    {
        $date = Carbon::parse($currentDate);
        
        return match($period) {
            'monthly' => $date->copy()->subMonth()->toDateString(),
            'quarterly' => $date->copy()->subQuarter()->toDateString(),
            'yearly' => $date->copy()->subYear()->toDateString(),
            'custom' => null, // Will be handled separately
            default => $date->copy()->subMonth()->toDateString(),
        };
    }
} 