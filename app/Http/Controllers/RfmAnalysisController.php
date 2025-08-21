<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\RfmReport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RfmAnalysisController extends Controller
{
    // --------------------
    // PAGES
    // --------------------

    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $activeConnection = $user->xeroConnections()->where('is_active', true)->first();
            
            if (!$activeConnection) {
                return redirect()->route('dashboard')->withErrors('Please connect a Xero organisation first.');
            }

            $summaryStats = $this->getSummaryStats($user->id, $activeConnection->tenant_id);

            $recentRfmData = RfmReport::where('user_id', $user->id)
                ->whereHas('client', function($q) use ($activeConnection) {
                    $q->where('tenant_id', $activeConnection->tenant_id);
                })
                ->orderBy('snapshot_date', 'desc')
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

            // Process data for the new chart structure
            $companies = [];
            $dateLabels = [];
            $allValues = collect();
            
            if ($rfmData->count() > 0) {
                // Generate monthly date labels
                $minDate = Carbon::parse($rfmData->min('date'))->startOfMonth();
                $maxDate = Carbon::parse($rfmData->max('date'))->startOfMonth();
                
                for ($d = $minDate->copy(); $d <= $maxDate; $d->addMonth()) {
                    $dateLabels[] = $d->format('M Y');
                }
                
                // Group by company and process data
                $byCompany = $rfmData->groupBy('client_name');
                $palette = ['#60A5FA','#F87171','#34D399','#F59E0B','#A78BFA','#F472B6','#06B6D4','#84CC16','#14B8A6','#FB923C','#8B5CF6','#22C55E','#F43F5E','#38BDF8','#EAB308'];
                
                $i = 0;
                foreach ($byCompany as $name => $rows) {
                    $byMonth = $rows->groupBy(fn($r) => Carbon::parse($r->date)->format('Y-m'));
                    $series = [];
                    
                    for ($d = $minDate->copy(); $d <= $maxDate; $d->addMonth()) {
                        $monthKey = $d->format('Y-m');
                        if (isset($byMonth[$monthKey])) {
                            $avg = round((float)$byMonth[$monthKey]->avg('rfm_score'), 2);
                            $series[] = $avg;
                            $allValues->push($avg);
                        } else {
                            $series[] = null;
                        }
                    }
                    
                    $companies[] = [
                        'name' => $name,
                        'data' => $series,
                        'color' => $palette[$i % count($palette)]
                    ];
                    $i++;
                }
                
                // Sort companies by average RFM score
                $companies = collect($companies)->sortByDesc(function($company) {
                    $validData = array_filter($company['data'], fn($v) => $v !== null);
                    return count($validData) > 0 ? array_sum($validData) / count($validData) : 0;
                })->values()->toArray();
            }
            
            $minValue = $allValues->isNotEmpty() ? $allValues->min() : 0;
            $maxValue = $allValues->isNotEmpty() ? $allValues->max() : 10;
            $range = max(1e-6, $maxValue - $minValue);

            return view('rfm.analysis.index', [
                'activeConnection' => $activeConnection,
                'summaryStats'     => $summaryStats,
                'recentRfmData'    => $recentRfmData,
                'rfmData'          => $rfmData,
                'companies'        => $companies,
                'dateLabels'       => $dateLabels,
                'minValue'         => $minValue,
                'maxValue'         => $maxValue,
                'range'            => $range,
            ]);
        } catch (\Exception $e) {
            Log::error('RFM Analysis index error: ' . $e->getMessage());
            return redirect()->route('dashboard')->withErrors('Failed to load RFM analysis. Please try again.');
        }
    }

    public function trends(Request $request)
    {
        try {
            $user = $request->user();
            $activeConnection = $user->xeroConnections()->where('is_active', true)->first();
            
            if (!$activeConnection) {
                return redirect()->route('dashboard')->withErrors('Please connect a Xero organisation first.');
            }

            // Inputs
            $monthsBack = (int) $request->get('months_back', 12);
            $metric     = $request->get('metric', 'rfm_score'); // rfm_score|r_score|f_score|m_score
            $limit      = (int) $request->get('limit', 12);     // top N customers to plot

            // Validate metric
            $allowed = ['rfm_score','r_score','f_score','m_score'];
            if (!in_array($metric, $allowed, true)) {
                $metric = 'rfm_score';
            }

            $dateCutoff = now()->subMonths($monthsBack)->startOfDay();

            // Pull rows
            $rows = RfmReport::select([
                    'rfm_reports.snapshot_date as date',
                    'clients.name as client_name',
                    'rfm_reports.rfm_score',
                    'rfm_reports.r_score',
                    'rfm_reports.f_score',
                    'rfm_reports.m_score',
                ])
                    ->join('clients', 'clients.id', '=', 'rfm_reports.client_id')
                ->where('rfm_reports.user_id', $user->id)
                    ->where('clients.tenant_id', $activeConnection->tenant_id)
                ->where('rfm_reports.snapshot_date', '>=', $dateCutoff)
                ->orderBy('rfm_reports.snapshot_date', 'asc')
                    ->get();

            // Fallback if empty: load all
            if ($rows->isEmpty()) {
                $rows = RfmReport::select([
                        'rfm_reports.snapshot_date as date',
                        'clients.name as client_name',
                        'rfm_reports.rfm_score',
                        'rfm_reports.r_score',
                        'rfm_reports.f_score',
                        'rfm_reports.m_score',
                    ])
                    ->join('clients', 'clients.id', '=', 'rfm_reports.client_id')
                    ->where('rfm_reports.user_id', $user->id)
                    ->where('clients.tenant_id', $activeConnection->tenant_id)
                    ->orderBy('rfm_reports.snapshot_date', 'asc')
                    ->get();
            }

            // X-axis labels (unique sorted dates)
            $labels = $rows->pluck('date')
                ->map(fn($d) => Carbon::parse($d)->toDateString())
                ->unique()->sort()->values();

            // Pick top-N customers by average of the chosen metric (keeps chart readable)
            $topCustomers = $rows->groupBy('client_name')
                ->map(fn($g) => (float) $g->avg($metric))
                ->sortDesc()
                ->keys()
                ->take($limit);

            $filtered = $rows->whereIn('client_name', $topCustomers);

            // Build one dataset per client, aligned to labels
            $byClient = $filtered->groupBy('client_name');
            $datasets = [];
            foreach ($byClient as $client => $clientRows) {
                $byDate = $clientRows->groupBy(fn($r) => Carbon::parse($r->date)->toDateString())
                                     ->map(fn($g) => round((float) $g->avg($metric), 2));
                $datasets[] = [
                    'label' => $client,
                    'data'  => $labels->map(fn($d) => $byDate[$d] ?? null)->values(), // null -> gaps
                ];
            }

            return view('rfm.analysis.trends', [
                'activeConnection' => $activeConnection,
                'labels'           => $labels,
                'datasets'         => $datasets,
                'metric'           => $metric,
                'monthsBack'       => $monthsBack,
                'limit'            => $limit,
            ]);
        } catch (\Exception $e) {
            Log::error('RFM Trends error: ' . $e->getMessage());
            return redirect()->route('dashboard')->withErrors('Failed to load RFM trends. Please try again.');
        }
    }

    public function business(Request $request)
    {
        try {
            $user = $request->user();
            $activeConnection = $user->xeroConnections()->where('is_active', true)->first();
            
            if (!$activeConnection) {
                return redirect()->route('dashboard')->withErrors('Please connect a Xero organisation first.');
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
                'rfmData'          => $rfmData,
            ]);
        } catch (\Exception $e) {
            Log::error('RFM Business error: ' . $e->getMessage());
            return redirect()->route('dashboard')->withErrors('Failed to load RFM business analysis. Please try again.');
        }
    }

    // Choose ONE snapshot per client per month, then count segments.
    // $from/$to are "YYYY-MM" strings; $includePartial lets you keep/exclude current month.
    public function getMonthlySegmentSeries(int $userId, string $tenantId, ?string $from = null, ?string $to = null, bool $includePartial = false)
    {
        try {
            $fromDate = $from
                ? Carbon::parse($from . '-01')->startOfMonth()
                : now()->subMonths(12)->startOfMonth();

            $toDate = $to
                ? Carbon::parse($to . '-01')->endOfMonth()
                : now()->endOfMonth();

            // Exclude the in-progress current month unless explicitly included
            if (!$includePartial && $toDate->isSameMonth(now())) {
                $toDate = now()->copy()->startOfMonth()->subDay()->endOfDay();
            }

            // Subquery: latest snapshot per client per month
            // Use database-agnostic date formatting
            $latestPerClientMonth = DB::table('rfm_reports as r')
                ->join('clients as c', 'c.id', '=', 'r.client_id')
                ->where('r.user_id', $userId)
                ->where('c.tenant_id', $tenantId)
                ->whereBetween('r.snapshot_date', [$fromDate, $toDate])
                ->selectRaw("
                    r.client_id,
                    DATE_FORMAT(r.snapshot_date, '%Y-%m') as ym,
                    MAX(r.snapshot_date) as last_date
                ")
                ->groupBy('ym', 'r.client_id');

            // Join back to get the actual rows for those latest timestamps
            $rows = DB::table('rfm_reports as x')
                ->joinSub($latestPerClientMonth, 'm', function ($j) {
                    $j->on('x.client_id', '=', 'm.client_id')->on('x.snapshot_date', '=', 'm.last_date');
                })
                ->selectRaw('m.ym, x.rfm_score, x.r_score, x.f_score, x.m_score')
                ->orderBy('m.ym')
                ->get();

            // Build month labels
            $months = $rows->pluck('ym')->unique()->sort()->values();
            $labels = $months->map(fn ($ym) => Carbon::createFromFormat('Y-m', $ym)->format('M Y'))->values()->toArray();

            // Bucket counts per month
            $series = [
                'Champions'        => [],
                'Loyal Customers'  => [],
                'At Risk'          => [],
                "Can't Lose"       => [],
                'Lost'             => [],
            ];

            foreach ($months as $ym) {
                $chunk = $rows->where('ym', $ym);
                $series['Champions'][]       = $chunk->where('rfm_score', '>=', 8)->count();
                $series['Loyal Customers'][] = $chunk->filter(fn ($r) => $r->rfm_score >= 6 && $r->rfm_score < 8)->count();
                $series['At Risk'][]         = $chunk->filter(fn ($r) => $r->rfm_score >= 4 && $r->rfm_score < 6)->count();
                $series["Can't Lose"][]      = $chunk->filter(fn ($r) => $r->rfm_score >= 2 && $r->rfm_score < 4)->count();
                $series['Lost'][]            = $chunk->filter(fn ($r) => $r->rfm_score < 2)->count();
            }

            return [
                'labels' => $labels,
                'months' => $months->toArray(), // YYYY-MM for tooltips if you need
                'series' => $series,
            ];
        } catch (\Exception $e) {
            Log::error('Monthly segment series error: ' . $e->getMessage());
            return [
                'labels' => [],
                'months' => [],
                'series' => [
                    'Champions' => [],
                    'Loyal Customers' => [],
                    'At Risk' => [],
                    "Can't Lose" => [],
                    'Lost' => [],
                ],
            ];
        }
    }

    // New method: Get percentile-based segmentation with transitions
    public function getAdvancedSegmentAnalysis(int $userId, string $tenantId, ?string $from = null, ?string $to = null)
    {
        try {
            $fromDate = $from
                ? Carbon::parse($from . '-01')->startOfMonth()
                : now()->subMonths(12)->startOfMonth();

            $toDate = $to
                ? Carbon::parse($to . '-01')->endOfMonth()
                : now()->subMonths(1)->endOfMonth(); // Exclude current month by default

            // Get monthly snapshots with percentile-based scoring
            $monthlyData = $this->getPercentileBasedSegments($userId, $tenantId, $fromDate, $toDate);
            
            // Calculate transitions between months
            $transitions = $this->getSegmentTransitions($userId, $tenantId, $fromDate, $toDate);
            
            // Calculate revenue by segment
            $revenueBySegment = $this->getRevenueBySegment($userId, $tenantId, $fromDate, $toDate);

            return [
                'monthlyData' => $monthlyData,
                'transitions' => $transitions,
                'revenueBySegment' => $revenueBySegment,
            ];
        } catch (\Exception $e) {
            Log::error('Advanced segment analysis error: ' . $e->getMessage());
            return [
                'monthlyData' => [],
                'transitions' => [],
                'revenueBySegment' => [],
            ];
        }
    }

    private function getPercentileBasedSegments(int $userId, string $tenantId, $fromDate, $toDate)
    {
        // Get all RFM data for the period
        $rfmData = DB::table('rfm_reports as r')
            ->join('clients as c', 'c.id', '=', 'r.client_id')
            ->where('r.user_id', $userId)
            ->where('c.tenant_id', $tenantId)
            ->whereBetween('r.snapshot_date', [$fromDate, $toDate])
            ->selectRaw('
                r.client_id,
                DATE_FORMAT(r.snapshot_date, "%Y-%m") as ym,
                r.r_score, r.f_score, r.m_score,
                r.rfm_score,
                MAX(r.snapshot_date) as last_date
            ')
            ->groupBy('r.client_id', 'ym')
            ->orderBy('ym')
            ->get();

        $months = $rfmData->pluck('ym')->unique()->sort()->values();
        $result = [];

        foreach ($months as $month) {
            $monthData = $rfmData->where('ym', $month);
            
            // Calculate percentiles for this month
            $rScores = $monthData->pluck('r_score')->filter()->sort();
            $fScores = $monthData->pluck('f_score')->filter()->sort();
            $mScores = $monthData->pluck('m_score')->filter()->sort();
            
            $rPercentiles = $this->calculatePercentiles($rScores);
            $fPercentiles = $this->calculatePercentiles($fScores);
            $mPercentiles = $this->calculatePercentiles($mScores);
            
            // Score each customer based on percentiles
            $segments = [
                'Champions' => 0,
                'Loyal Customers' => 0,
                'At Risk' => 0,
                "Can't Lose" => 0,
                'Lost' => 0,
            ];
            
            foreach ($monthData as $customer) {
                $segment = $this->getPercentileBasedSegment($customer, $rPercentiles, $fPercentiles, $mPercentiles);
                $segments[$segment]++;
            }
            
            $result[$month] = $segments;
        }

        return $result;
    }

    private function calculatePercentiles($scores)
    {
        if ($scores->count() < 4) {
            return [20 => 1, 40 => 2, 60 => 3, 80 => 4, 100 => 5];
        }
        
        $count = $scores->count();
        $percentiles = [
            20 => $scores->get(floor($count * 0.2)),
            40 => $scores->get(floor($count * 0.4)),
            60 => $scores->get(floor($count * 0.6)),
            80 => $scores->get(floor($count * 0.8)),
            100 => $scores->last(),
        ];
        
        // Ensure we have valid values and handle edge cases
        foreach ($percentiles as $key => $value) {
            if ($value === null || $value <= 0) {
                $percentiles[$key] = 1;
            }
        }
        
        return $percentiles;
    }

    private function getPercentileBasedSegment($customer, $rPercentiles, $fPercentiles, $mPercentiles)
    {
        // Score each component based on percentiles
        $rScore = $this->getPercentileScore($customer->r_score, $rPercentiles);
        $fScore = $this->getPercentileScore($customer->f_score, $fPercentiles);
        $mScore = $this->getPercentileScore($customer->m_score, $mPercentiles);
        
        // Weighted score: R*0.4 + F*0.3 + M*0.3
        $weightedScore = ($rScore * 0.4) + ($fScore * 0.3) + ($mScore * 0.3);
        
        // Map to segments
        if ($weightedScore >= 4.5) return 'Champions';
        if ($weightedScore >= 3.5) return 'Loyal Customers';
        if ($weightedScore >= 2.5) return 'At Risk';
        if ($weightedScore >= 1.5) return "Can't Lose";
        return 'Lost';
    }

    private function getPercentileScore($value, $percentiles)
    {
        if ($value <= $percentiles[20]) return 1;
        if ($value <= $percentiles[40]) return 2;
        if ($value <= $percentiles[60]) return 3;
        if ($value <= $percentiles[80]) return 4;
        return 5;
    }

    private function getSegmentTransitions(int $userId, string $tenantId, $fromDate, $toDate)
    {
        // Get consecutive months data
        $months = [];
        $current = $fromDate->copy();
        while ($current <= $toDate) {
            $months[] = $current->format('Y-m');
            $current->addMonth();
        }
        
        $transitions = [];
        
        for ($i = 1; $i < count($months); $i++) {
            $prevMonth = $months[$i-1];
            $currMonth = $months[$i];
            
            // Get segments for both months
            $prevSegments = $this->getPercentileBasedSegments($userId, $tenantId, 
                Carbon::parse($prevMonth . '-01'), 
                Carbon::parse($prevMonth . '-01')->endOfMonth());
            
            $currSegments = $this->getPercentileBasedSegments($userId, $tenantId,
                Carbon::parse($currMonth . '-01'),
                Carbon::parse($currMonth . '-01')->endOfMonth());
            
            // Calculate transitions (simplified - in reality you'd track individual customers)
            $transitions[$currMonth] = [
                'from' => $prevMonth,
                'to' => $currMonth,
                'changes' => [
                    'Champions' => ($currSegments[$currMonth]['Champions'] ?? 0) - ($prevSegments[$prevMonth]['Champions'] ?? 0),
                    'Loyal Customers' => ($currSegments[$currMonth]['Loyal Customers'] ?? 0) - ($prevSegments[$prevMonth]['Loyal Customers'] ?? 0),
                    'At Risk' => ($currSegments[$currMonth]['At Risk'] ?? 0) - ($prevSegments[$prevMonth]['At Risk'] ?? 0),
                    "Can't Lose" => ($currSegments[$currMonth]["Can't Lose"] ?? 0) - ($prevSegments[$prevMonth]["Can't Lose"] ?? 0),
                    'Lost' => ($currSegments[$currMonth]['Lost'] ?? 0) - ($prevSegments[$prevMonth]['Lost'] ?? 0),
                ]
            ];
        }
        
        return $transitions;
    }

    private function getRevenueBySegment(int $userId, string $tenantId, $fromDate, $toDate)
    {
        // Get revenue data by segment (simplified - you'd need to join with invoice data)
        $revenueData = DB::table('rfm_reports as r')
            ->join('clients as c', 'c.id', '=', 'r.client_id')
            ->where('r.user_id', $userId)
            ->where('c.tenant_id', $tenantId)
            ->whereBetween('r.snapshot_date', [$fromDate, $toDate])
            ->selectRaw('
                DATE_FORMAT(r.snapshot_date, "%Y-%m") as ym,
                r.rfm_score,
                COUNT(*) as customer_count
            ')
            ->groupBy('ym', 'rfm_score')
            ->orderBy('ym')
            ->get();
        
        $result = [];
        foreach ($revenueData as $row) {
            $segment = $this->getSegmentFromScore($row->rfm_score);
            if (!isset($result[$row->ym])) {
                $result[$row->ym] = [
                    'Champions' => 0,
                    'Loyal Customers' => 0,
                    'At Risk' => 0,
                    "Can't Lose" => 0,
                    'Lost' => 0,
                ];
            }
            $result[$row->ym][$segment] += $row->customer_count;
        }
        
        return $result;
    }

    private function getSegmentFromScore($rfmScore)
    {
        if ($rfmScore >= 8) return 'Champions';
        if ($rfmScore >= 6) return 'Loyal Customers';
        if ($rfmScore >= 4) return 'At Risk';
        if ($rfmScore >= 2) return "Can't Lose";
        return 'Lost';
    }

    public function segments(Request $request)
    {
        try {
            $user = $request->user();
            $activeConnection = $user->xeroConnections()->where('is_active', true)->first();
            
            if (!$activeConnection) {
                return redirect()->route('dashboard')->withErrors('Please connect a Xero organisation first.');
            }

            $from = $request->string('from')->toString();            // "YYYY-MM" optional
            $to   = $request->string('to')->toString();              // "YYYY-MM" optional
            $includePartial = (bool) $request->boolean('partial');   // include current month?

            $segTS = $this->getMonthlySegmentSeries($user->id, $activeConnection->tenant_id, $from, $to, $includePartial);

            return view('rfm.analysis.segments', [
                'activeConnection'    => $activeConnection,
                'labels'              => $segTS['labels'],
                'segmentSeries'       => $segTS['series'],
                // keep these if you still use them elsewhere:
                'segmentData'         => $this->getSegmentData($user->id, $activeConnection->tenant_id),
                'segmentDistribution' => $this->getSegmentDistribution($user->id, $activeConnection->tenant_id),
                'from'                => $from,
                'to'                  => $to,
                'includePartial'      => $includePartial,
            ]);
        } catch (\Exception $e) {
            Log::error('RFM Segments error: ' . $e->getMessage());
            return redirect()->route('dashboard')->withErrors('Failed to load RFM segments. Please try again.');
        }
    }

    public function predictive(Request $request)
    {
        $user = $request->user();
        $activeConnection = $user->getActiveXeroConnection();
        if (!$activeConnection) {
            return redirect()->route('dashboard')->withErrors('Please connect a Xero organisation first.');
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
            return redirect()->route('dashboard')->withErrors('Please connect a Xero organisation first.');
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
            return redirect()->route('dashboard')->withErrors('Please connect a Xero organisation first.');
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
        try {
            $user = $request->user();
            $activeConnection = $user->xeroConnections()->where('is_active', true)->first();
            
            if (!$activeConnection) {
                return response()->json(['error' => 'No active organisation'], 400);
            }

            return response()->json(
                $this->getSummaryStats($user->id, $activeConnection->tenant_id)
            );
        } catch (\Exception $e) {
            Log::error('Dashboard summary error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load summary data'], 500);
        }
    }

    public function trendSeries(Request $request)
    {
        try {
            $user = $request->user();
            $activeConnection = $user->xeroConnections()->where('is_active', true)->first();
            
            if (!$activeConnection) {
                return response()->json(['error' => 'No active organisation'], 400);
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
        } catch (\Exception $e) {
            Log::error('Trend series error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load trend data'], 500);
        }
    }

    public function segmentSummary(Request $request)
    {
        try {
            $user = $request->user();
            $activeConnection = $user->xeroConnections()->where('is_active', true)->first();
            
            if (!$activeConnection) {
                return response()->json(['error' => 'No active organisation'], 400);
            }

            return response()->json([
                'segments'     => $this->getSegmentData($user->id, $activeConnection->tenant_id),
                'distribution' => $this->getSegmentDistribution($user->id, $activeConnection->tenant_id),
            ]);
        } catch (\Exception $e) {
            Log::error('Segment summary error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load segment data'], 500);
        }
    }

    public function predictiveSeries(Request $request)
    {
        $user = $request->user();
        $activeConnection = $user->getActiveXeroConnection();
        if (!$activeConnection) {
            return response()->json(['error' => 'No active organisation'], 400);
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
            return response()->json(['error' => 'No active organisation'], 400);
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
            return response()->json(['error' => 'No active organisation'], 400);
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
            return response()->json(['error' => 'No active organisation'], 400);
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
        $currentRfm = RfmReport::where('user_id', $userId)
            ->whereHas('client', function($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })
            ->orderBy('snapshot_date', 'desc')
            ->get();

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
                'rfm_reports.m_score'
            )
            ->join('clients', 'clients.id', '=', 'rfm_reports.client_id')
            ->where('rfm_reports.user_id', $userId)
            ->where('clients.tenant_id', $tenantId)
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
                'current_avg'       => 0,
                'previous_avg'      => 0,
                'change_percentage' => 0,
                'trend_direction'   => 'stable',
            ];
        }

        // Ordered date keys
        $dates = $trendData->keys()->sort()->values();

        // Last 3 vs previous 3 (not overlapping)
        $last3 = $dates->slice(-3);
        $prev3 = $dates->slice(-6, 3);

        $currentAvg  = $last3->map(fn($d) => $trendData[$d]->avg($metric))->avg() ?? 0;
        $previousAvg = $prev3->map(fn($d) => $trendData[$d]->avg($metric))->avg() ?? 0;

        $changePct = ($previousAvg > 0)
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
        $cur = RfmReport::where('user_id', $userId)
            ->whereHas('client', function($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })
            ->orderBy('snapshot_date', 'desc')
            ->get();

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
        $cur = RfmReport::where('user_id', $userId)
            ->whereHas('client', function($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })
            ->orderBy('snapshot_date', 'desc')
            ->get();

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

}
