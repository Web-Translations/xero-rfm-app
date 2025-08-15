<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\RfmReport;
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
            return redirect()->route('dashboard')->withErrors('Please connect a Xero organization first.');
        }

        return view('rfm.reports.index', [
            'activeConnection' => $activeConnection,
        ]);
    }

    public function generate(Request $request)
    {
        $user = $request->user();
        
        // Get active connection
        $activeConnection = $user->getActiveXeroConnection();
        if (!$activeConnection) {
            return redirect()->route('dashboard')->withErrors('Please connect a Xero organization first.');
        }

        $reportType = $request->get('report_type', 'summary');
        $dateRange = $request->get('date_range', 'current');

        // Get RFM data based on report type and date range
        if ($dateRange === 'current') {
            $rfmData = RfmReport::getCurrentScoresForUser($user->id, $activeConnection->tenant_id)->get();
        } else {
            $rfmData = RfmReport::getForSnapshotDate($user->id, $dateRange, $activeConnection->tenant_id)->get();
        }

        return view('rfm.reports.show', [
            'rfmData' => $rfmData,
            'reportType' => $reportType,
            'dateRange' => $dateRange,
            'activeConnection' => $activeConnection,
        ]);
    }
} 