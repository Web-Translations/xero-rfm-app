<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\RfmReport;
use App\Services\Rfm\RfmCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RfmController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $search = trim((string) $request->get('q', ''));
        $viewMode = $request->get('view', 'current'); // 'current' or a specific date

        // Get RFM data based on view mode
        if ($viewMode === 'current') {
            // Get the latest RFM report for each client using the new structure
            $query = RfmReport::getLatestForUser($user->id);
        } else {
            // Get historical snapshot for specific date (viewMode is the date)
            $query = RfmReport::getForSnapshotDate($user->id, $viewMode);
        }

        // Apply search filter
        if ($search !== '') {
            $query->where('client_name', 'like', '%' . $search . '%');
        }

        $rows = $query->paginate(15)->withQueryString();

        // Get available snapshot dates for view mode dropdown
        $availableSnapshots = RfmReport::getAvailableSnapshotDates($user->id);

        // Get total counts for user feedback
        $totalClients = Client::where('user_id', $user->id)->count();
        $filteredCount = $rows->total();

        return view('rfm.index', [
            'rows' => $rows,
            'search' => $search,
            'viewMode' => $viewMode,
            'availableSnapshots' => $availableSnapshots,
            'totalClients' => $totalClients,
            'filteredCount' => $filteredCount,
        ]);
    }

    public function sync(Request $request, RfmCalculator $calculator)
    {
        $user = $request->user();
        $action = $request->get('action', 'current'); // 'current' or 'historical'

        if ($action === 'current') {
            // Calculate current RFM scores
            $result = $calculator->computeSnapshot($user->id);
            $status = "Calculated current RFM scores for {$result['computed']} clients.";
        } else {
            // Calculate historical snapshots for trend analysis
            $monthsBack = (int) $request->get('months_back', 12);
            $results = $calculator->computeHistoricalSnapshots($user->id, $monthsBack);
            $totalComputed = array_sum(array_column($results, 'computed'));
            $status = "Created historical snapshots for {$totalComputed} client records over {$monthsBack} months.";
        }

        return redirect()->route('rfm.index')->with('status', $status);
    }
}

