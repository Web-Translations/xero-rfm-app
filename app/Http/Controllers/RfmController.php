<?php

namespace App\Http\Controllers;

use App\Models\Client;
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
        $viewMode = $request->get('view', 'current'); // 'current' or 'historical'
        $snapshotDate = $request->get('snapshot_date', '');

        // Get RFM data based on view mode
        if ($viewMode === 'current') {
            // Get the most recent snapshot for each client (by created_at, not by ID)
            // Use a simpler approach: get the latest created_at for each client, then join
            $latestReports = DB::table('rfm_reports')
                ->select('client_id', DB::raw('MAX(created_at) as latest_created'))
                ->where('user_id', $user->id)
                ->groupBy('client_id');
            
            $query = DB::table('rfm_reports as r')
                ->select([
                    'r.*',
                    'c.name as client_name'
                ])
                ->join('clients as c', 'c.id', '=', 'r.client_id')
                ->joinSub($latestReports, 'latest', function($join) {
                    $join->on('r.client_id', '=', 'latest.client_id')
                         ->on('r.created_at', '=', 'latest.latest_created');
                })
                ->where('r.user_id', $user->id);
        } else {
            // Get historical snapshot for specific date
            $query = DB::table('rfm_reports as r')
                ->select([
                    'r.*',
                    'c.name as client_name'
                ])
                ->join('clients as c', 'c.id', '=', 'r.client_id')
                ->where('r.user_id', $user->id)
                ->where('r.period_end', $snapshotDate);
        }

        // Apply search filter
        if ($search !== '') {
            $query->where('c.name', 'like', '%' . $search . '%');
        }

        $rows = $query->orderByDesc('r.rfm_score')
            ->orderBy('c.name')
            ->paginate(15)
            ->withQueryString();

        // Get available snapshot dates for historical view
        $availableSnapshots = DB::table('rfm_reports')
            ->select('period_end')
            ->where('user_id', $user->id)
            ->distinct()
            ->orderByDesc('period_end')
            ->pluck('period_end');

        // Get total counts for user feedback
        $totalClients = Client::where('user_id', $user->id)->count();
        $filteredCount = $rows->total();

        return view('rfm.index', [
            'rows' => $rows,
            'search' => $search,
            'viewMode' => $viewMode,
            'snapshotDate' => $snapshotDate,
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

