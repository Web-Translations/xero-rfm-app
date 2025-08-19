<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\RfmReport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RfmAnalysisController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get active connection
        if (!$user->getActiveXeroConnection()) {
            return redirect()->route('dashboard')->withErrors('Please connect a Xero organisation first.');
        }

        // Get available snapshot dates for analysis
        $availableSnapshots = RfmReport::getAvailableSnapshotDates($user->id, $activeConnection->tenant_id);

        return view('rfm.analysis.index', [
            'activeConnection' => $activeConnection,
            'availableSnapshots' => $availableSnapshots,
        ]);
    }

    public function trends(Request $request)
    {
        $user = $request->user();
        
        // Get active connection
        $activeConnection = $user->getActiveXeroConnection();
        if (!$activeConnection) {
            return redirect()->route('dashboard')->withErrors('Please connect a Xero organisation first.');
        }

        $clientId = $request->get('client_id');
        $monthsBack = $request->get('months_back', 12);

        // Get trend data for specific client or all clients
        if ($clientId) {
            $trendData = RfmReport::where('rfm_reports.user_id', $user->id)
                ->join('clients', 'clients.id', '=', 'rfm_reports.client_id')
                ->where('clients.tenant_id', $activeConnection->tenant_id)
                ->where('rfm_reports.client_id', $clientId)
                ->orderBy('rfm_reports.snapshot_date', 'desc')
                ->limit($monthsBack)
                ->get();
        } else {
            $trendData = RfmReport::where('rfm_reports.user_id', $user->id)
                ->join('clients', 'clients.id', '=', 'rfm_reports.client_id')
                ->where('clients.tenant_id', $activeConnection->tenant_id)
                ->orderBy('rfm_reports.snapshot_date', 'desc')
                ->limit($monthsBack)
                ->get();
        }

        return view('rfm.analysis.trends', [
            'trendData' => $trendData,
            'activeConnection' => $activeConnection,
            'clientId' => $clientId,
        ]);
    }
} 