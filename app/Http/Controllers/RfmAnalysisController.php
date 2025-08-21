<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\RfmReport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RfmAnalysisController extends Controller
{
    // --------------------
    // PAGES
    // --------------------

    public function index(Request $request)
    {
        $user = $request->user();
        $activeConnection = $user->getActiveXeroConnection();
        if (!$activeConnection) {
            return redirect()->route('dashboard')->withErrors('Please connect a Xero organization first.');
        }

        $summaryStats = $this->getSummaryStats($user->id, $activeConnection->tenant_id);

        $recentRfmData = RfmReport::getCurrentScoresForUser($user->id, $activeConnection->tenant_id)
            ->limit(10)
            ->get();

        // Get RFM data for charts - last 12 months
        $monthsBack = 12;
        $dateCutoff = now()->subMonths($monthsBack)->startOfDay();
        
        $rfmData = RfmReport::select([
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
            ->orderBy('rfm_reports.snapshot_date', 'asc')
            ->get();

        return view('rfm.analysis.index', [
            'activeConnection' => $activeConnection,
            'summaryStats'     => $summaryStats,
            'recentRfmData'    => $recentRfmData,
            'rfmData'          => $rfmData,
        ]);
    }

    public function trends(Request $request)
    {
        $user = $request->user();
        $activeConnection = $user->getActiveXeroConnection();
        if (!$activeConnection) {
            return redirect()->route('dashboard')->withErrors('Please connect a Xero organisation first.');
        }

        // Get RFM data for trends chart - last 12 months
        $monthsBack = 12;
        $dateCutoff = now()->subMonths($monthsBack)->startOfDay();
        
        $rfmData = RfmReport::select([
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
            ->orderBy('rfm_reports.snapshot_date', 'asc')
            ->get();

        return view('rfm.analysis.trends', [
            'activeConnection' => $activeConnection,
            'rfmData' => $rfmData,
        ]);
    }

    public function business(Request $request)
    {
        $user = $request->user();
        $activeConnection = $user->getActiveXeroConnection();
        if (!$activeConnection) {
            return redirect()->route('dashboard')->withErrors('Please connect a Xero organization first.');
        }

        // Get RFM data for business analytics - last 12 months
        $monthsBack = 12;
        $dateCutoff = now()->subMonths($monthsBack)->startOfDay();
        
        $rfmData = RfmReport::select([
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
            ->orderBy('rfm_reports.snapshot_date', 'asc')
            ->get();

        return view('rfm.analysis.business', [
            'activeConnection' => $activeConnection,
            'rfmData' => $rfmData,
        ]);
    }



    public function segments(Request $request)
    {
        $user = $request->user();
        $activeConnection = $user->getActiveXeroConnection();
        if (!$activeConnection) {
            return redirect()->route('dashboard')->withErrors('Please connect a Xero organization first.');
        }

        $segmentData        = $this->getSegmentData($user->id, $activeConnection->tenant_id);
        $segmentDistribution= $this->getSegmentDistribution($user->id, $activeConnection->tenant_id);

        return view('rfm.analysis.segments', [
            'activeConnection'     => $activeConnection,
            'segmentData'          => $segmentData,
            'segmentDistribution'  => $segmentDistribution,
        ]);
    }

    public function predictive(Request $request)
    {
        $user = $request->user();
        $activeConnection = $user->getActiveXeroConnection();
        if (!$activeConnection) {
            return redirect()->route('dashboard')->withErrors('Please connect a Xero organization first.');
        }

        // JS can also hit predictiveSeries() for fresh data
        $predictiveData = $this->getPredictiveData($user->id, $activeConnection->tenant_id);

        return view('rfm.analysis.predictive', [
            'activeConnection' => $activeConnection,
            'predictiveData'   => $predictiveData,
        ]);
    }

    public function cohort(Request $request)
    {
        $user = $request->user();
        $activeConnection = $user->getActiveXeroConnection();
        if (!$activeConnection) {
            return redirect()->route('dashboard')->withErrors('Please connect a Xero organization first.');
        }

        $cohortData = $this->getCohortData($user->id, $activeConnection->tenant_id);

        return view('rfm.analysis.cohort', [
            'activeConnection' => $activeConnection,
            'cohortData'       => $cohortData,
        ]);
    }

    public function comparative(Request $request)
    {
        $user = $request->user();
        $activeConnection = $user->getActiveXeroConnection();
        if (!$activeConnection) {
            return redirect()->route('dashboard')->withErrors('Please connect a Xero organization first.');
        }

        $period1 = $request->get('period1', now()->subMonths(6)->toDateString());
        $period2 = $request->get('period2', now()->toDateString());

        $comparativeData = $this->getComparativeData($user->id, $activeConnection->tenant_id, $period1, $period2);

        return view('rfm.analysis.comparative', [
            'activeConnection' => $activeConnection,
            'comparativeData'  => $comparativeData,
            'period1'          => $period1,
            'period2'          => $period2,
        ]);
    }

    // --------------------
    // PUBLIC JSON ENDPOINTS (for front-end charts)
    // --------------------

    public function dashboardSummary(Request $request)
    {
        $user = $request->user();
        $activeConnection = $user->getActiveXeroConnection();
        if (!$activeConnection) {
            return response()->json(['error' => 'No active organization'], 400);
        }

        return response()->json(
            $this->getSummaryStats($user->id, $activeConnection->tenant_id)
        );
    }

    public function trendSeries(Request $request)
    {
        $user = $request->user();
        $activeConnection = $user->getActiveXeroConnection();
        if (!$activeConnection) {
            return response()->json(['error' => 'No active organization'], 400);
        }

        $clientId   = $request->get('client_id');
        $monthsBack = (int) $request->get('months_back', 12);
        $metric     = $request->get('metric', 'rfm_score'); // rfm_score|r_score|f_score|m_score|txn_count|monetary_sum

        $trendData  = $this->getTrendData($user->id, $activeConnection->tenant_id, $clientId, $monthsBack, $metric);
        $trendStats = $this->getTrendStats($trendData, $metric);

        // Flatten for chart.js or apexcharts: [{date, avg, count}]
        $series = $trendData->map(function ($rows, $date) use ($metric) {
            return [
                'date'  => Carbon::parse($date)->toDateString(),
                'avg'   => round($rows->avg($metric), 2),
                'count' => $rows->count(),
            ];
        })->values();

        return response()->json([
            'series' => $series,
            'stats'  => $trendStats,
            'metric' => $metric,
        ]);
    }

    public function segmentSummary(Request $request)
    {
        $user = $request->user();
        $activeConnection = $user->getActiveXeroConnection();
        if (!$activeConnection) {
            return response()->json(['error' => 'No active organization'], 400);
        }

        return response()->json([
            'segments'     => $this->getSegmentData($user->id, $activeConnection->tenant_id),
            'distribution' => $this->getSegmentDistribution($user->id, $activeConnection->tenant_id),
        ]);
    }

    public function predictiveSeries(Request $request)
    {
        $user = $request->user();
        $activeConnection = $user->getActiveXeroConnection();
        if (!$activeConnection) {
            return response()->json(['error' => 'No active organization'], 400);
        }

        return response()->json(
            $this->getPredictiveData($user->id, $activeConnection->tenant_id)
        );
    }

    public function cohortSeries(Request $request)
    {
        $user = $request->user();
        $activeConnection = $user->getActiveXeroConnection();
        if (!$activeConnection) {
            return response()->json(['error' => 'No active organization'], 400);
        }

        return response()->json(
            $this->getCohortData($user->id, $activeConnection->tenant_id)
        );
    }

    public function comparativeSeries(Request $request)
    {
        $user = $request->user();
        $activeConnection = $user->getActiveXeroConnection();
        if (!$activeConnection) {
            return response()->json(['error' => 'No active organization'], 400);
        }

        $period1 = $request->get('period1', now()->subMonths(6)->toDateString());
        $period2 = $request->get('period2', now()->toDateString());

        return response()->json(
            $this->getComparativeData($user->id, $activeConnection->tenant_id, $period1, $period2)
        );
    }

    public function allClientsComparisonSeries(Request $request)
    {
        $user = $request->user();
        $activeConnection = $user->getActiveXeroConnection();
        if (!$activeConnection) {
            return response()->json(['error' => 'No active organization'], 400);
        }

        $monthsBack = (int) $request->get('months_back', 12);

        return response()->json(
            $this->getAllClientsComparisonData($user->id, $activeConnection->tenant_id, $monthsBack)
        );
    }

    // --------------------
    // PRIVATE HELPERS
    // --------------------

    private function getSummaryStats($userId, $tenantId)
    {
        $currentRfm = RfmReport::getCurrentScoresForUser($userId, $tenantId)->get();

        return [
            'total_clients'    => $currentRfm->count(),
            'avg_rfm_score'    => round((float) $currentRfm->avg('rfm_score'), 2),
            'high_value'       => $currentRfm->where('rfm_score', '>=', 8)->count(),
            'at_risk'          => $currentRfm->where('rfm_score', '<=', 3)->count(),
            'recent_activity'  => $currentRfm->where('months_since_last', '<=', 3)->count(),
        ];
    }

    private function getTrendData($userId, $tenantId, $clientId = null, $monthsBack = 12, $metric = 'rfm_score')
    {
        $cutoff = now()->subMonths($monthsBack)->startOfDay();

        $query = RfmReport::select(
                'rfm_reports.snapshot_date',
                'clients.name as client_name',
                'rfm_reports.rfm_score',
                'rfm_reports.r_score',
                'rfm_reports.f_score',
                'rfm_reports.m_score',
                'rfm_reports.monetary_sum',
                'rfm_reports.txn_count'
            )
            ->join('clients', 'clients.id', '=', 'rfm_reports.client_id')
            ->where('rfm_reports.user_id', $userId)
            ->where('rfm_reports.tenant_id', $tenantId)
            ->where('rfm_reports.snapshot_date', '>=', $cutoff)
            ->orderBy('rfm_reports.snapshot_date', 'asc');

        if ($clientId) {
            $query->where('rfm_reports.client_id', $clientId);
        }

        return $query->get()->groupBy(function ($row) {
            return Carbon::parse($row->snapshot_date)->toDateString();
        });
    }

    private function getTrendStats($trendData, $metric)
    {
        if ($trendData->isEmpty()) {
            return [
                'current_avg'      => 0,
                'previous_avg'     => 0,
                'change_percentage'=> 0,
                'trend_direction'  => 'stable',
            ];
        }

        // Ordered date keys
        $dates = $trendData->keys()->sort()->values();

        // Last 3 vs previous 3 (not overlapping)
        $last3     = $dates->slice(-3);
        $prev3     = $dates->slice(-6, 3);

        $currentAvg = $last3->map(fn($d) => $trendData[$d]->avg($metric))->avg() ?? 0;
        $previousAvg= $prev3->map(fn($d) => $trendData[$d]->avg($metric))->avg() ?? 0;

        $changePct  = ($previousAvg > 0)
            ? (($currentAvg - $previousAvg) / $previousAvg) * 100
            : 0;

        return [
            'current_avg'       => round((float) $currentAvg, 2),
            'previous_avg'      => round((float) $previousAvg, 2),
            'change_percentage' => round((float) $changePct, 1),
            'trend_direction'   => $changePct > 0 ? 'up' : ($changePct < 0 ? 'down' : 'stable'),
        ];
    }

    private function getSegmentData($userId, $tenantId)
    {
        $cur = RfmReport::getCurrentScoresForUser($userId, $tenantId)->get();

        return [
            'champions'       => $cur->where('rfm_score', '>=', 8)->count(),
            'loyal_customers' => $cur->whereBetween('rfm_score', [6, 7.99])->count(),
            'at_risk'         => $cur->whereBetween('rfm_score', [4, 5.99])->count(),
            'cant_lose'       => $cur->whereBetween('rfm_score', [2, 3.99])->count(),
            'lost'            => $cur->where('rfm_score', '<', 2)->count(),
        ];
    }

    private function getSegmentDistribution($userId, $tenantId)
    {
        $cur = RfmReport::getCurrentScoresForUser($userId, $tenantId)->get();

        return $cur->groupBy(function ($r) {
            if ($r->rfm_score >= 8) return 'Champions';
            if ($r->rfm_score >= 6) return 'Loyal Customers';
            if ($r->rfm_score >= 4) return 'At Risk';
            if ($r->rfm_score >= 2) return "Can't Lose";
            return 'Lost';
        })->map->count();
    }

    private function getPredictiveData($userId, $tenantId)
    {
        $historical = RfmReport::select(
                'rfm_reports.snapshot_date',
                'rfm_reports.rfm_score',
                'rfm_reports.months_since_last',
                'rfm_reports.txn_count'
            )
            ->where('rfm_reports.user_id', $userId)
            ->where('rfm_reports.tenant_id', $tenantId)
            ->where('rfm_reports.snapshot_date', '>=', now()->subMonths(24)->startOfDay())
            ->orderBy('rfm_reports.snapshot_date', 'asc')
            ->get();

        $byDate = $historical->groupBy(function ($r) {
            return Carbon::parse($r->snapshot_date)->toDateString();
        });

        $churnRisk = $byDate->map(function ($rows, $date) {
            $count = max(1, $rows->count());
            $low   = $rows->where('rfm_score', '<=', 3)->count();
            return [
                'date'      => $date,
                'avg_rfm'   => round((float) $rows->avg('rfm_score'), 2),
                'churn_risk'=> round(($low / $count) * 100, 2),
            ];
        })->values();

        return [
            'historical_data' => $historical,
            'churn_risk'      => $churnRisk,
            'predicted_churn' => $this->predictChurn($historical),
        ];
    }

    private function getCohortData($userId, $tenantId)
    {
        $rows = RfmReport::select(
                'clients.name',
                'rfm_reports.snapshot_date',
                'rfm_reports.rfm_score',
                'rfm_reports.txn_count'
            )
            ->join('clients', 'clients.id', '=', 'rfm_reports.client_id')
            ->where('rfm_reports.user_id', $userId)
            ->where('rfm_reports.tenant_id', $tenantId)
            ->orderBy('rfm_reports.snapshot_date', 'asc')
            ->get();

        return $rows->groupBy('name');
    }

    private function getComparativeData($userId, $tenantId, $period1, $period2)
    {
        $p1 = RfmReport::getForSnapshotDate($userId, $period1, $tenantId)->get();
        $p2 = RfmReport::getForSnapshotDate($userId, $period2, $tenantId)->get();

        $fmt = function ($c) {
            return [
                'avg_rfm'     => round((float) $c->avg('rfm_score'), 2),
                'total_clients'=> $c->count(),
                'high_value'  => $c->where('rfm_score', '>=', 8)->count(),
                'at_risk'     => $c->where('rfm_score', '<=', 3)->count(),
            ];
        };

        return ['period1' => $fmt($p1), 'period2' => $fmt($p2)];
    }

    private function predictChurn($historicalData)
    {
        // Expect ascending order
        $hist = $historicalData->values();

        // Last 6 vs previous 6
        $recent  = $hist->slice(-6);
        $older   = $hist->slice(-12, 6);

        $rCount  = max(1, $recent->count());
        $oCount  = max(1, $older->count());

        $recentChurn = ($recent->where('rfm_score', '<=', 3)->count() / $rCount) * 100;
        $olderChurn  = ($older->where('rfm_score', '<=', 3)->count() / $oCount) * 100;

        $trend = $recentChurn - $olderChurn;

        return [
            'current_rate'   => round($recentChurn, 1),
            'predicted_rate' => round($recentChurn + $trend, 1),
            'trend'          => $trend > 0 ? 'increasing' : ($trend < 0 ? 'decreasing' : 'stable'),
        ];
        }

    private function getAllClientsComparisonData($userId, $tenantId, $monthsBack)
    {
        $cutoff = now()->subMonths($monthsBack)->startOfDay();

        $rfmData = RfmReport::select(
                'rfm_reports.snapshot_date',
                'clients.name as client_name',
                'rfm_reports.rfm_score',
                'rfm_reports.r_score',
                'rfm_reports.f_score',
                'rfm_reports.m_score',
                'rfm_reports.monetary_sum',
                'rfm_reports.txn_count',
                'rfm_reports.months_since_last'
            )
            ->join('clients', 'clients.id', '=', 'rfm_reports.client_id')
            ->where('rfm_reports.user_id', $userId)
            ->where('rfm_reports.tenant_id', $tenantId)
            ->where('rfm_reports.snapshot_date', '>=', $cutoff)
            ->orderBy('rfm_reports.snapshot_date', 'asc')
            ->get();

        $grouped = $rfmData->groupBy(function ($r) {
            return Carbon::parse($r->snapshot_date)->toDateString();
        });

        $out = [];
        foreach ($grouped as $date => $records) {
            $avgR = $records->avg(fn($r) => max(0, 10 - (int) $r->months_since_last));
            $avgF = $records->avg(fn($r) => min((int) $r->txn_count, 10));

            $monetaryValues = $records->pluck('monetary_sum')->filter();
            $minM = $monetaryValues->min();
            $maxM = $monetaryValues->max();
            $avgM = 0;

            if ($maxM !== null && $minM !== null && $maxM > $minM) {
                $avgM = $records->avg(function ($r) use ($minM, $maxM) {
                    $m = (float) ($r->monetary_sum ?? 0);
                    return $m > 0 ? (($m - $minM) / ($maxM - $minM)) * 10 : 0;
                });
            }

            $avgRfm = ($avgR + $avgF + $avgM) / 3;

            $out[$date] = [
                'r_score'           => round((float) $avgR, 2),
                'f_score'           => round((float) $avgF, 2),
                'm_score'           => round((float) $avgM, 2),
                'rfm_score'         => round((float) $avgRfm, 2),
                'client_count'      => $records->count(),
                'total_monetary'    => round((float) $records->sum('monetary_sum'), 2),
                'total_transactions'=> (int) $records->sum('txn_count'),
            ];
        }

        return $out;
    }

    /**
     * Get advanced segment analysis data for the RFM analysis page
     */
    public function getAdvancedSegmentAnalysis($userId, $tenantId)
    {
        // Get RFM data for the last 12 months
        $monthsBack = 12;
        $dateCutoff = now()->subMonths($monthsBack)->startOfDay();
        
        $rfmData = RfmReport::select([
                'rfm_reports.snapshot_date as date',
                'rfm_reports.r_score',
                'rfm_reports.f_score',
                'rfm_reports.m_score',
                'rfm_reports.rfm_score',
                'rfm_reports.client_id',
                'clients.name as client_name',
            ])
            ->join('clients', 'clients.id', '=', 'rfm_reports.client_id')
            ->where('rfm_reports.user_id', $userId)
            ->where('clients.tenant_id', $tenantId)
            ->where('rfm_reports.snapshot_date', '>=', $dateCutoff)
            ->where('rfm_reports.rfm_score', '>', 0)
            ->orderBy('rfm_reports.snapshot_date', 'asc')
            ->get();

        // Group by month and calculate segment distribution
        $monthlyData = [];
        
        foreach ($rfmData as $record) {
            $month = Carbon::parse($record->date)->format('Y-m');
            
            if (!isset($monthlyData[$month])) {
                $monthlyData[$month] = [
                    'Champions' => 0,
                    'Loyal Customers' => 0,
                    'At Risk' => 0,
                    "Can't Lose" => 0,
                    'Lost' => 0
                ];
            }
            
            // Categorize by RFM score
            $score = $record->rfm_score;
            if ($score >= 8) {
                $monthlyData[$month]['Champions']++;
            } elseif ($score >= 6) {
                $monthlyData[$month]['Loyal Customers']++;
            } elseif ($score >= 4) {
                $monthlyData[$month]['At Risk']++;
            } elseif ($score >= 2) {
                $monthlyData[$month]["Can't Lose"]++;
            } else {
                $monthlyData[$month]['Lost']++;
            }
        }

        return [
            'monthlyData' => $monthlyData
        ];
    }
}
