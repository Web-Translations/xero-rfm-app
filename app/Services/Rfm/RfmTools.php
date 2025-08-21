<?php

namespace App\Services\Rfm;

use App\Models\Client;
use App\Models\RfmReport;
use App\Models\RfmConfiguration;
use App\Models\XeroInvoice;
use App\Models\ExcludedInvoice;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RfmTools
{
    /**
     * Compute comprehensive KPIs for RFM analysis
     */
    public function computeKpis(
        int $userId, 
        string $tenantId, 
        string $currentSnapshotDate, 
        ?string $comparisonSnapshotDate, 
        RfmConfiguration $config
    ): array {
        // Get current period data
        $currentData = $this->getPeriodData($userId, $tenantId, $currentSnapshotDate, $config);
        
        // Get comparison period data (if available)
        $comparisonData = null;
        if ($comparisonSnapshotDate) {
            $comparisonData = $this->getPeriodData($userId, $tenantId, $comparisonSnapshotDate, $config);
        }

        // Calculate enhanced KPIs
        $kpis = [
            'current_period' => $currentData,
            'comparison_period' => $comparisonData,
            'analysis' => $this->analyzeTrends($currentData, $comparisonData),
            'segments' => $this->analyzeSegments($currentData),
            'concentration' => $this->analyzeConcentration($currentData),
            'movement' => $this->analyzeCustomerMovement($currentData, $comparisonData),
            'insights' => $this->generateInsights($currentData, $comparisonData),
            'customer_movement_details' => $this->getCustomerMovementDetails($userId, $tenantId, $currentSnapshotDate, $comparisonSnapshotDate),
            'historical_trends' => $this->getHistoricalTrends($userId, $tenantId, $currentSnapshotDate),
            'risk_analysis' => $this->analyzeRiskFactors($currentData),
            'opportunities' => $this->identifyOpportunities($currentData, $comparisonData),
        ];

        return $kpis;
    }

    /**
     * Get comprehensive data for a specific period
     */
    private function getPeriodData(int $userId, string $tenantId, string $snapshotDate, RfmConfiguration $config): array
    {
        // Get RFM reports for this snapshot
        $rfmReports = RfmReport::getForSnapshotDate($userId, $snapshotDate, $tenantId)->get();
        
        // Filter out truly inactive customers (RFM score = 0)
        $activeRfmReports = $rfmReports->filter(function($report) {
            return (float) $report->rfm_score > 0; // Only customers with any activity
        });
        
        // Get invoice data for this period (using config windows)
        $windowStart = Carbon::parse($snapshotDate)->subMonths($config->frequency_period_months);
        $invoices = $this->getInvoiceData($userId, $tenantId, $windowStart, $snapshotDate);

        // Calculate basic metrics using ACTIVE customers only
        $totalRevenue = $invoices->sum('total');
        $totalTransactions = $invoices->count();
        $activeCustomers = $activeRfmReports->count(); // Only count customers with meaningful activity
        $averageRfm = $activeRfmReports->avg('rfm_score') ?? 0; // Average of active customers only

        // Calculate AOV
        $averageOrderValue = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;

        // Calculate revenue per customer (using active customers for more accurate metrics)
        $revenuePerCustomer = $activeCustomers > 0 ? $totalRevenue / $activeCustomers : 0;

        // Get customer movement data
        $customerMovement = $this->getCustomerMovement($userId, $tenantId, $snapshotDate, $config);

        // Enhanced metrics using active customers for better insights
        $highValueCustomers = $activeRfmReports->where('rfm_score', '>=', 8)->count();
        $atRiskCustomers = $activeRfmReports->where('rfm_score', '<=', 1)->count();
        $customersUnder1k = $activeRfmReports->where('rfm_score', '<', 2)->count();
        
        // Calculate customer lifetime value indicators
        $repeatCustomers = $invoices->groupBy('contact_id')->filter(function($customerInvoices) {
            return $customerInvoices->count() > 1;
        })->count();
        
        $repeatCustomerRate = $activeCustomers > 0 ? ($repeatCustomers / $activeCustomers) * 100 : 0;

        return [
            'snapshot_date' => $snapshotDate,
            'total_revenue' => $totalRevenue,
            'total_transactions' => $totalTransactions,
            'active_customers' => $activeCustomers,
            'total_customers' => $rfmReports->count(), // Total including inactive
            'average_rfm' => round($averageRfm, 2),
            'average_order_value' => round($averageOrderValue, 2),
            'revenue_per_customer' => round($revenuePerCustomer, 2),
            'customer_movement' => $customerMovement,
            'rfm_reports' => $activeRfmReports, // Use active customers for analysis
            'all_rfm_reports' => $rfmReports, // All reports for reference
            'invoices' => $invoices,
            'high_value_customers' => $highValueCustomers,
            'at_risk_customers' => $atRiskCustomers,
            'customers_under_1k' => $customersUnder1k,
            'repeat_customers' => $repeatCustomers,
            'repeat_customer_rate' => round($repeatCustomerRate, 1),
        ];
    }

    /**
     * Get detailed customer movement analysis
     */
    private function getCustomerMovementDetails(int $userId, string $tenantId, string $currentDate, ?string $previousDate): array
    {
        if (!$previousDate) {
            return [
                'ranking_changes' => [],
                'top_50_movements' => [],
                'inactive_customers' => [],
                'lost_customers' => [],
                'new_customers' => [],
                'returned_customers' => [],
            ];
        }

        $currentReports = RfmReport::getForSnapshotDate($userId, $currentDate, $tenantId)->get();
        $previousReports = RfmReport::getForSnapshotDate($userId, $previousDate, $tenantId)->get();

        // Get current and previous top 50 customers
        $currentTop50 = $currentReports->sortByDesc('rfm_score')->take(50);
        $previousTop50 = $previousReports->sortByDesc('rfm_score')->take(50);

        $currentTop50Ids = $currentTop50->pluck('client_id')->toArray();
        $previousTop50Ids = $previousTop50->pluck('client_id')->toArray();

        // Customers who moved into Top 50
        $movedIntoTop50 = $currentTop50->filter(function($report) use ($previousTop50Ids) {
            return !in_array($report->client_id, $previousTop50Ids);
        })->map(function($report) use ($tenantId, $userId) {
            return [
                'client_name' => $report->client_name,
                'current_rfm' => (float) $report->rfm_score,
                'current_rank' => $this->getCustomerRank($report->client_id, $report->snapshot_date, $userId, $tenantId),
            ];
        })->values();

        // Customers who fell out of Top 50
        $fellOutOfTop50 = $previousTop50->filter(function($report) use ($currentTop50Ids) {
            return !in_array($report->client_id, $currentTop50Ids);
        })->map(function($report) use ($tenantId, $userId) {
            return [
                'client_name' => $report->client_name,
                'previous_rfm' => (float) $report->rfm_score,
                'previous_rank' => $this->getCustomerRank($report->client_id, $report->snapshot_date, $userId, $tenantId),
            ];
        })->values();

        // Recently lost customers (were active in previous period but inactive now)
        $recentlyLostCustomers = $previousReports->filter(function($report) use ($currentReports) {
            $currentReport = $currentReports->where('client_id', $report->client_id)->first();
            // Was active in previous period (RFM > 0) but inactive now (RFM = 0)
            return (float) $report->rfm_score > 0 && (!$currentReport || (float) $currentReport->rfm_score == 0);
        })->map(function($report) {
            return [
                'client_name' => $report->client_name,
                'previous_rfm' => (float) $report->rfm_score,
                'last_active' => $report->snapshot_date,
                'months_inactive' => $this->calculateMonthsInactive($report->snapshot_date),
            ];
        })->sortBy('months_inactive') // Sort by most recently lost first
        ->values();

        // Lost customers (appeared in previous but not current)
        $lostCustomers = $previousReports->filter(function($report) use ($currentReports) {
            return !$currentReports->where('client_id', $report->client_id)->count();
        })->map(function($report) {
            return [
                'client_name' => $report->client_name,
                'previous_rfm' => (float) $report->rfm_score,
                'last_seen' => $report->snapshot_date,
            ];
        })->values();

        // New customers (appeared in current but not in previous period)
        $newCustomers = $currentReports->filter(function($report) use ($previousReports) {
            return !$previousReports->where('client_id', $report->client_id)->count();
        })->map(function($report) {
            return [
                'client_name' => $report->client_name,
                'current_rfm' => (float) $report->rfm_score,
                'first_seen' => $report->snapshot_date,
            ];
        })->values();

        // Returned customers (were inactive in previous period but active in current)
        $returnedCustomers = $currentReports->filter(function($report) use ($previousReports) {
            $previousReport = $previousReports->where('client_id', $report->client_id)->first();
            // Customer was in previous period but inactive (RFM = 0) and now active (RFM > 0)
            return $previousReport && (float) $previousReport->rfm_score == 0 && (float) $report->rfm_score > 0;
        })->map(function($report) {
            return [
                'client_name' => $report->client_name,
                'current_rfm' => (float) $report->rfm_score,
                'returned_date' => $report->snapshot_date,
            ];
        })->values();

        // Ranking changes for existing customers
        $rankingChanges = [];
        foreach ($currentReports as $currentReport) {
            $previousReport = $previousReports->where('client_id', $currentReport->client_id)->first();
            if ($previousReport) {
                $currentRank = $this->getCustomerRank($currentReport->client_id, $currentDate, $userId, $tenantId);
                $previousRank = $this->getCustomerRank($previousReport->client_id, $previousDate, $userId, $tenantId);
                
                // Include customers who improved from inactive (RFM = 0) to active
                if ($currentRank || ((float) $currentReport->rfm_score > 0 && (float) $previousReport->rfm_score == 0)) {
                    $rankingChanges[] = [
                        'client_name' => $currentReport->client_name,
                        'current_rank' => $currentRank ?? 'N/A',
                        'previous_rank' => $previousRank ?? 'N/A',
                        'rank_change' => $previousRank && $currentRank ? $previousRank - $currentRank : 'New Active',
                        'current_rfm' => (float) $currentReport->rfm_score,
                        'previous_rfm' => (float) $previousReport->rfm_score,
                        'rfm_change' => (float) $currentReport->rfm_score - (float) $previousReport->rfm_score,
                    ];
                }
            }
        }

        // Sort ranking changes - prioritize "New Active" customers, then by RFM improvement
        usort($rankingChanges, function($a, $b) {
            // "New Active" customers should be at the top
            if ($a['rank_change'] === 'New Active' && $b['rank_change'] !== 'New Active') {
                return -1;
            }
            if ($b['rank_change'] === 'New Active' && $a['rank_change'] !== 'New Active') {
                return 1;
            }
            
            // For numeric changes, sort by absolute change
            if (is_numeric($a['rank_change']) && is_numeric($b['rank_change'])) {
                return abs($b['rank_change']) <=> abs($a['rank_change']);
            }
            
            // For other cases, sort by RFM improvement
            return $b['rfm_change'] <=> $a['rfm_change'];
        });

        return [
            'ranking_changes' => array_slice($rankingChanges, 0, 20), // Top 20 changes
            'top_50_movements' => [
                'moved_in' => $movedIntoTop50->take(10)->toArray(),
                'fell_out' => $fellOutOfTop50->take(10)->toArray(),
            ],
            'recently_lost_customers' => $recentlyLostCustomers->take(15)->toArray(),
            'lost_customers' => $lostCustomers->take(15)->toArray(),
            'new_customers' => $newCustomers->take(15)->toArray(),
            'returned_customers' => $returnedCustomers->take(15)->toArray(),
        ];
    }

    /**
     * Get customer rank for a specific date (only active customers)
     */
    private function getCustomerRank(int $clientId, string $snapshotDate, int $userId, string $tenantId): ?int
    {
        $rank = RfmReport::where('rfm_reports.user_id', $userId)
            ->join('clients', 'clients.id', '=', 'rfm_reports.client_id')
            ->where('clients.tenant_id', $tenantId)
            ->where('rfm_reports.snapshot_date', $snapshotDate)
            ->where('rfm_reports.rfm_score', '>', 0) // Only rank active customers
            ->orderByDesc('rfm_reports.rfm_score')
            ->pluck('rfm_reports.client_id')
            ->search($clientId);
        
        return $rank !== false ? $rank + 1 : null;
    }

    /**
     * Get historical trends
     */
    private function getHistoricalTrends(int $userId, string $tenantId, string $currentDate): array
    {
        // Get last 6 snapshot dates
        $snapshotDates = RfmReport::where('rfm_reports.user_id', $userId)
            ->join('clients', 'clients.id', '=', 'rfm_reports.client_id')
            ->where('clients.tenant_id', $tenantId)
            ->distinct()
            ->pluck('rfm_reports.snapshot_date')
            ->sort()
            ->take(-6)
            ->values();

                $trends = [];
        foreach ($snapshotDates as $date) {
            $reports = RfmReport::getForSnapshotDate($userId, $date, $tenantId)->get();
            
            // Filter for active customers only (RFM > 0) for more meaningful metrics
            $activeReports = $reports->filter(function($report) {
                return (float) $report->rfm_score > 0;
            });
            
            $trends[] = [
                'date' => $date,
                'formatted_date' => Carbon::parse($date)->format('M j, Y'), // Show exact date
                'total_customers' => $activeReports->count(),
                'average_rfm' => round($activeReports->avg('rfm_score') ?? 0, 2),
                'high_value_customers' => $activeReports->where('rfm_score', '>=', 8)->count(),
                'at_risk_customers' => $activeReports->where('rfm_score', '<=', 1)->count(),
            ];
        }

        return $trends;
    }

    /**
     * Analyze risk factors
     */
    private function analyzeRiskFactors(array $data): array
    {
        $reports = $data['rfm_reports']; // This now contains only active customers
        $allReports = $data['all_rfm_reports']; // All reports including inactive
        $totalActiveCustomers = $reports->count();
        $totalCustomers = $allReports->count();
        
        $risks = [];
        
        // High concentration risk (using actual revenue data)
        if ($totalActiveCustomers > 0) {
            $invoices = $data['invoices'] ?? collect();
            if ($invoices->isNotEmpty()) {
                $customerRevenue = $invoices->groupBy('contact_id')
                    ->map(function($customerInvoices) {
                        return $customerInvoices->sum('total');
                    })
                    ->sortByDesc(function($revenue) {
                        return $revenue;
                    });
                
                $totalRevenue = $customerRevenue->sum();
                $top10Revenue = $customerRevenue->take(10)->sum();
                $top10Share = $totalRevenue > 0 ? ($top10Revenue / $totalRevenue) * 100 : 0;
                
                if ($top10Share > 70) {
                    $risks[] = [
                        'type' => 'concentration',
                        'severity' => 'high',
                        'title' => 'High Revenue Concentration',
                        'description' => "Top 10 customers represent " . round($top10Share, 1) . "% of revenue. This creates significant business risk if any key customers leave.",
                        'impact' => 'High dependency on few customers',
                        'recommendation' => 'Diversify customer base and develop mid-tier customers',
                    ];
                }
            }
        }
        
        // High customer churn risk (only active customers)
        $atRiskCustomers = $reports->where('rfm_score', '<=', 1)->count();
        $atRiskPercentage = $totalActiveCustomers > 0 ? ($atRiskCustomers / $totalActiveCustomers) * 100 : 0;
        
        if ($atRiskPercentage > 50) {
            $risks[] = [
                'type' => 'churn',
                'severity' => 'high',
                'title' => 'High Customer Churn Risk',
                'description' => round($atRiskPercentage, 1) . "% of customers are at risk of churning (RFM score ≤ 1).",
                'impact' => 'Potential significant revenue loss',
                'recommendation' => 'Implement immediate retention campaigns for at-risk customers',
            ];
        }
        
        // Low repeat customer rate
        $repeatCustomerRate = $data['repeat_customer_rate'] ?? 0;
        if ($repeatCustomerRate < 20) {
            $risks[] = [
                'type' => 'retention',
                'severity' => 'medium',
                'title' => 'Low Repeat Customer Rate',
                'description' => "Only " . round($repeatCustomerRate, 1) . "% of customers are repeat buyers.",
                'impact' => 'High customer acquisition costs',
                'recommendation' => 'Focus on customer success and relationship building',
            ];
        }
        
        // Low overall customer performance
        $averageRfm = $reports->avg('rfm_score') ?? 0;
        if ($averageRfm < 3) {
            $risks[] = [
                'type' => 'performance',
                'severity' => 'medium',
                'title' => 'Low Overall Customer Performance',
                'description' => "Average RFM score of " . round($averageRfm, 2) . " indicates poor customer engagement.",
                'impact' => 'Declining customer value over time',
                'recommendation' => 'Review customer experience and engagement strategies',
            ];
        }
        
        // High inactive customer base
        $inactiveCustomers = $totalCustomers - $totalActiveCustomers;
        $inactivePercentage = $totalCustomers > 0 ? ($inactiveCustomers / $totalCustomers) * 100 : 0;
        
        if ($inactivePercentage > 30) {
            $risks[] = [
                'type' => 'inactive',
                'severity' => 'medium',
                'title' => 'High Inactive Customer Base',
                'description' => round($inactivePercentage, 1) . "% of customers are inactive (RFM score = 0).",
                'impact' => 'Diluted metrics and potential data quality issues',
                'recommendation' => 'Clean up customer database and focus on active customers',
            ];
        }
        
        return $risks;
    }

    /**
     * Identify business opportunities
     */
    private function identifyOpportunities(array $currentData, ?array $comparisonData): array
    {
        $opportunities = [];
        $reports = $currentData['rfm_reports'];

        // High-value customers with growth potential
        $highValueCustomers = $reports->where('rfm_score', '>=', 8);
        if ($highValueCustomers->count() < 10) {
            $opportunities[] = [
                'type' => 'growth',
                'priority' => 'high',
                'title' => 'Develop More High-Value Customers',
                'description' => "Only {$highValueCustomers->count()} customers are high-value (RFM 8-10). Focus on mid-tier customers with growth potential.",
                'potential_impact' => 'Significant revenue growth',
                'action_items' => [
                    'Identify mid-tier customers with high potential',
                    'Develop personalized upselling strategies',
                    'Increase engagement with promising customers',
                ],
            ];
        }

        // Customers with high frequency but low monetary
        $highFreqLowMonetary = $reports->where('f_score', '>=', 7)->where('m_score', '<', 5);
        if ($highFreqLowMonetary->count() > 0) {
            $opportunities[] = [
                'type' => 'upselling',
                'priority' => 'medium',
                'title' => 'Upselling Opportunities',
                'description' => "{$highFreqLowMonetary->count()} customers buy frequently but have low order values. Perfect candidates for upselling.",
                'potential_impact' => 'Increase average order value',
                'action_items' => [
                    'Identify cross-selling opportunities',
                    'Develop premium product offerings',
                    'Create loyalty programs for frequent buyers',
                ],
            ];
        }

        // At-risk customers with high historical value
        $atRiskHighValue = $reports->where('rfm_score', '<=', 2)->where('m_score', '>=', 5);
        if ($atRiskHighValue->count() > 0) {
            $opportunities[] = [
                'type' => 'retention',
                'priority' => 'high',
                'title' => 'Win Back High-Value Customers',
                'description' => "{$atRiskHighValue->count()} customers who previously spent heavily are now at risk. High potential for recovery.",
                'potential_impact' => 'Recover significant revenue',
                'action_items' => [
                    'Personalized re-engagement campaigns',
                    'Special offers for returning customers',
                    'Direct outreach from senior team members',
                ],
            ];
        }

        // New customers with immediate potential
        $newCustomers = $reports->where('f_score', '=', 1)->where('m_score', '>=', 3);
        if ($newCustomers->count() > 0) {
            $opportunities[] = [
                'type' => 'acquisition',
                'priority' => 'medium',
                'title' => 'Nurture Promising New Customers',
                'description' => "{$newCustomers->count()} new customers show immediate potential with decent order values.",
                'potential_impact' => 'Build long-term customer relationships',
                'action_items' => [
                    'Welcome and onboarding programs',
                    'Quick follow-up communications',
                    'Educational content about additional services',
                ],
            ];
        }

        return $opportunities;
    }

    /**
     * Get invoice data for a specific period
     */
    private function getInvoiceData(int $userId, string $tenantId, Carbon $startDate, string $endDate): Collection
    {
        // Get excluded invoice IDs
        $excludedInvoiceIds = ExcludedInvoice::getExcludedInvoiceIds($userId, $tenantId);

        return XeroInvoice::query()
            ->where('user_id', $userId)
            ->where('tenant_id', $tenantId)
            ->where('date', '>=', $startDate->toDateString())
            ->where('date', '<=', $endDate)
            ->when(!empty($excludedInvoiceIds), function ($query) use ($excludedInvoiceIds) {
                $query->whereNotIn('invoice_id', $excludedInvoiceIds);
            })
            ->get();
    }

    /**
     * Analyze customer movement between periods
     */
    private function getCustomerMovement(int $userId, string $tenantId, string $snapshotDate, RfmConfiguration $config): array
    {
        // Get current and previous snapshots
        $currentReports = RfmReport::getForSnapshotDate($userId, $snapshotDate, $tenantId)->get();
        
        // Get previous snapshot for comparison
        $previousSnapshot = RfmReport::getAvailableSnapshotDates($userId, $tenantId)
            ->filter(function($date) use ($snapshotDate) {
                return $date < $snapshotDate;
            })
            ->first();

        if (!$previousSnapshot) {
            return [
                'new_customers' => 0,
                'returned_customers' => 0,
                'lost_customers' => 0,
                'churn_rate' => 0,
                'retention_rate' => 100,
            ];
        }

        $previousReports = RfmReport::getForSnapshotDate($userId, $previousSnapshot, $tenantId)->get();
        
        // Filter for active customers only (RFM > 0)
        $currentActive = $currentReports->filter(function($report) {
            return (float) $report->rfm_score > 0;
        });
        
        $previousActive = $previousReports->filter(function($report) {
            return (float) $report->rfm_score > 0;
        });
        
        // Calculate customer movement
        $currentActiveIds = $currentActive->pluck('client_id')->toArray();
        $previousActiveIds = $previousActive->pluck('client_id')->toArray();
        
        // Calculate customer movement with clear definitions
        $currentActiveIds = $currentActive->pluck('client_id')->toArray();
        $previousActiveIds = $previousActive->pluck('client_id')->toArray();
        
        // Retained customers: were active in previous period AND still active now
        $retainedCustomers = $currentActive->filter(function($report) use ($previousActiveIds) {
            return in_array($report->client_id, $previousActiveIds);
        })->count();
        
        // New customers: in current active but not in previous period at all (completely new)
        $newCustomers = $currentActive->filter(function($report) use ($previousReports) {
            return !$previousReports->where('client_id', $report->client_id)->count();
        })->count();
        
        // Returned customers: existed in previous period but were inactive (RFM = 0), now active
        $returnedCustomers = $currentActive->filter(function($report) use ($previousReports) {
            $previousReport = $previousReports->where('client_id', $report->client_id)->first();
            return $previousReport && (float) $previousReport->rfm_score == 0;
        })->count();
        
        // Lost customers: were active in previous period but inactive now
        $lostCustomers = $previousActive->filter(function($report) use ($currentActiveIds) {
            return !in_array($report->client_id, $currentActiveIds);
        })->count();
        
        // Calculate rates
        $previousActiveCount = $previousActive->count();
        $churnRate = $previousActiveCount > 0 ? ($lostCustomers / $previousActiveCount) * 100 : 0;
        $retentionRate = $previousActiveCount > 0 ? ($retainedCustomers / $previousActiveCount) * 100 : 100;
        
        return [
            'retained_customers' => $retainedCustomers,
            'new_customers' => $newCustomers,
            'returned_customers' => $returnedCustomers,
            'lost_customers' => $lostCustomers,
            'churn_rate' => round($churnRate, 1),
            'retention_rate' => round($retentionRate, 1),
        ];
    }

    /**
     * Analyze RFM segments
     */
    private function analyzeSegments(array $data): array
    {
        $reports = $data['rfm_reports']; // This contains active customers only
        $allReports = $data['all_rfm_reports']; // All customers including inactive
        
        $segments = [
            'high_value' => ['count' => 0, 'revenue_share' => 0, 'avg_rfm' => 0],
            'mid_value' => ['count' => 0, 'revenue_share' => 0, 'avg_rfm' => 0],
            'low_value' => ['count' => 0, 'revenue_share' => 0, 'avg_rfm' => 0],
            'at_risk' => ['count' => 0, 'revenue_share' => 0, 'avg_rfm' => 0],
            'inactive' => ['count' => 0, 'revenue_share' => 0, 'avg_rfm' => 0],
        ];

        // Analyze active customers for detailed segments
        foreach ($reports as $report) {
            $rfmScore = (float) $report->rfm_score;
            
            if ($rfmScore >= 8) {
                $segments['high_value']['count']++;
                $segments['high_value']['avg_rfm'] += $rfmScore;
            } elseif ($rfmScore >= 5) {
                $segments['mid_value']['count']++;
                $segments['mid_value']['avg_rfm'] += $rfmScore;
            } elseif ($rfmScore >= 2) {
                $segments['low_value']['count']++;
                $segments['low_value']['avg_rfm'] += $rfmScore;
            } else {
                $segments['at_risk']['count']++;
                $segments['at_risk']['avg_rfm'] += $rfmScore;
            }
        }

        // Add inactive customers as separate segment
        $inactiveCustomers = $allReports->where('rfm_score', 0)->count();
        $segments['inactive']['count'] = $inactiveCustomers;

        // Calculate averages
        foreach ($segments as $key => $segment) {
            if ($segment['count'] > 0) {
                $segments[$key]['avg_rfm'] = round($segment['avg_rfm'] / $segment['count'], 2);
            }
        }

        return $segments;
    }

    /**
     * Analyze revenue concentration
     */
    private function analyzeConcentration(array $data): array
    {
        $reports = $data['rfm_reports']->sortByDesc('rfm_score');
        $totalRevenue = $data['total_revenue'];
        $invoices = $data['invoices'];
        
        if ($totalRevenue <= 0) {
            return [
                'top_10_share' => 0,
                'top_50_share' => 0,
                'customers_to_80_percent' => 0,
                'gini_coefficient' => 0,
            ];
        }

        // Calculate actual revenue per customer using invoice data
        $customerRevenue = $invoices->groupBy('contact_id')
            ->map(function($customerInvoices) {
                return $customerInvoices->sum('total');
            })
            ->sortByDesc(function($revenue) {
                return $revenue;
            });

        // Calculate top customer shares using actual revenue
        $top10Revenue = $customerRevenue->take(10)->sum();
        $top50Revenue = $customerRevenue->take(50)->sum();

        $top10Share = $totalRevenue > 0 ? ($top10Revenue / $totalRevenue) * 100 : 0;
        $top50Share = $totalRevenue > 0 ? ($top50Revenue / $totalRevenue) * 100 : 0;

        // Calculate customers needed for 80% of revenue
        $customersTo80Percent = $this->calculateCustomersTo80PercentRevenue($customerRevenue, $totalRevenue);

        return [
            'top_10_share' => round($top10Share, 1),
            'top_50_share' => round($top50Share, 1),
            'customers_to_80_percent' => $customersTo80Percent,
            'gini_coefficient' => $this->calculateGiniCoefficientRevenue($customerRevenue),
        ];
    }

    /**
     * Calculate customers needed for 80% of revenue (using actual revenue data)
     */
    private function calculateCustomersTo80PercentRevenue(Collection $customerRevenue, float $totalRevenue): int
    {
        $targetRevenue = $totalRevenue * 0.8;
        $cumulativeRevenue = 0;
        $customerCount = 0;

        foreach ($customerRevenue as $revenue) {
            $cumulativeRevenue += $revenue;
            $customerCount++;
            
            if ($cumulativeRevenue >= $targetRevenue) {
                break;
            }
        }

        return $customerCount;
    }

    /**
     * Calculate customers needed for 80% of revenue (legacy method using RFM scores)
     */
    private function calculateCustomersTo80Percent(Collection $reports): int
    {
        $totalRfm = $reports->sum('rfm_score');
        $targetRfm = $totalRfm * 0.8;
        $cumulativeRfm = 0;
        $customerCount = 0;

        foreach ($reports->sortByDesc('rfm_score') as $report) {
            $cumulativeRfm += (float) $report->rfm_score;
            $customerCount++;
            
            if ($cumulativeRfm >= $targetRfm) {
                break;
            }
        }

        return $customerCount;
    }

    /**
     * Calculate Gini coefficient for revenue distribution (using actual revenue data)
     */
    private function calculateGiniCoefficientRevenue(Collection $customerRevenue): float
    {
        if ($customerRevenue->isEmpty()) {
            return 0;
        }

        $revenues = $customerRevenue->sort()->values();
        $n = $revenues->count();
        
        if ($n <= 1) {
            return 0;
        }
        
        $sum = 0;

        for ($i = 0; $i < $n; $i++) {
            $sum += ($n - $i) * $revenues[$i];
        }

        $total = $revenues->sum();
        if ($total == 0) {
            return 0;
        }

        // Calculate Gini coefficient and ensure it's positive
        $gini = (2 * $sum) / ($n * $total) - ($n + 1) / $n;
        
        // Ensure the result is between 0 and 1
        $gini = max(0, min(1, abs($gini)));
        
        return round($gini, 3);
    }

    /**
     * Calculate Gini coefficient for revenue distribution (legacy method using RFM scores)
     */
    private function calculateGiniCoefficient(Collection $reports): float
    {
        if ($reports->isEmpty()) {
            return 0;
        }

        $scores = $reports->pluck('rfm_score')->sort()->values();
        $n = $scores->count();
        
        if ($n <= 1) {
            return 0;
        }
        
        $sum = 0;

        for ($i = 0; $i < $n; $i++) {
            $sum += ($n - $i) * $scores[$i];
        }

        $total = $scores->sum();
        if ($total == 0) {
            return 0;
        }

        // Calculate Gini coefficient and ensure it's positive
        $gini = (2 * $sum) / ($n * $total) - ($n + 1) / $n;
        
        // Ensure the result is between 0 and 1
        $gini = max(0, min(1, abs($gini)));
        
        return round($gini, 3);
    }

    /**
     * Analyze customer movement between periods
     */
    private function analyzeCustomerMovement(?array $currentData, ?array $comparisonData): array
    {
        if (!$comparisonData) {
            return [
                'improvers' => [],
                'decliners' => [],
                'stable' => [],
            ];
        }

        $currentReports = collect($currentData['rfm_reports']);
        $comparisonReports = collect($comparisonData['rfm_reports']);

        $improvers = [];
        $decliners = [];
        $stable = [];

        foreach ($currentReports as $currentReport) {
            $comparisonReport = $comparisonReports->where('client_id', $currentReport->client_id)->first();
            
            if ($comparisonReport) {
                $change = (float) $currentReport->rfm_score - (float) $comparisonReport->rfm_score;
                
                if ($change > 0.5) {
                    $improvers[] = [
                        'client_name' => $currentReport->client_name,
                        'current_rfm' => (float) $currentReport->rfm_score,
                        'previous_rfm' => (float) $comparisonReport->rfm_score,
                        'change' => round($change, 1),
                    ];
                } elseif ($change < -0.5) {
                    $decliners[] = [
                        'client_name' => $currentReport->client_name,
                        'current_rfm' => (float) $currentReport->rfm_score,
                        'previous_rfm' => (float) $comparisonReport->rfm_score,
                        'change' => round($change, 1),
                    ];
                } else {
                    $stable[] = [
                        'client_name' => $currentReport->client_name,
                        'current_rfm' => (float) $currentReport->rfm_score,
                        'previous_rfm' => (float) $comparisonReport->rfm_score,
                        'change' => round($change, 1),
                    ];
                }
            }
        }

        // Sort by absolute change
        usort($improvers, fn($a, $b) => abs((float)$b['change']) <=> abs((float)$a['change']));
        usort($decliners, fn($a, $b) => abs((float)$b['change']) <=> abs((float)$a['change']));

        return [
            'improvers' => array_slice($improvers, 0, 10), // Top 10
            'decliners' => array_slice($decliners, 0, 10), // Top 10
            'stable' => array_slice($stable, 0, 10),
        ];
    }

    /**
     * Analyze trends between periods
     */
    private function analyzeTrends(array $currentData, ?array $comparisonData): array
    {
        if (!$comparisonData) {
            return [
                'revenue_change' => 0,
                'aov_change' => 0,
                'customer_change' => 0,
                'rfm_change' => 0,
                'trends' => [],
            ];
        }

        $revenueChange = $this->calculatePercentageChange(
            $comparisonData['total_revenue'], 
            $currentData['total_revenue']
        );
        
        $aovChange = $this->calculatePercentageChange(
            $comparisonData['average_order_value'], 
            $currentData['average_order_value']
        );
        
        $customerChange = $this->calculatePercentageChange(
            $comparisonData['active_customers'], 
            $currentData['active_customers']
        );
        
        $rfmChange = $this->calculatePercentageChange(
            $comparisonData['average_rfm'], 
            $currentData['average_rfm']
        );

        // Generate trend insights
        $trends = [];
        
        if ($revenueChange > 5) {
            $trends[] = ['type' => 'positive', 'message' => 'Strong revenue growth'];
        } elseif ($revenueChange < -5) {
            $trends[] = ['type' => 'negative', 'message' => 'Revenue declining'];
        }
        
        if ($aovChange > 3) {
            $trends[] = ['type' => 'positive', 'message' => 'Average order value increasing'];
        } elseif ($aovChange < -3) {
            $trends[] = ['type' => 'negative', 'message' => 'Average order value declining'];
        }

        return [
            'revenue_change' => round($revenueChange, 1),
            'aov_change' => round($aovChange, 1),
            'customer_change' => round($customerChange, 1),
            'rfm_change' => round($rfmChange, 1),
            'trends' => $trends,
        ];
    }

    /**
     * Generate business insights
     */
    private function generateInsights(array $currentData, ?array $comparisonData): array
    {
        $insights = [];
        
        // Revenue insights
        if ($currentData['total_revenue'] > 0) {
            if ($currentData['average_order_value'] < 1000) {
                $insights[] = [
                    'type' => 'warning',
                    'category' => 'AOV',
                    'message' => 'Average order value is below £1,000 - consider upselling strategies',
                    'priority' => 'medium'
                ];
            }
            
            if ($currentData['revenue_per_customer'] < 5000) {
                $insights[] = [
                    'type' => 'info',
                    'category' => 'Revenue',
                    'message' => 'Revenue per customer is £' . number_format($currentData['revenue_per_customer']) . ' - opportunity for growth',
                    'priority' => 'low'
                ];
            }
        }

        // Customer concentration insights
        $concentration = $this->analyzeConcentration($currentData);
        if ($concentration['top_10_share'] > 50) {
            $insights[] = [
                'type' => 'warning',
                'category' => 'Concentration',
                'message' => 'Top 10 customers represent ' . $concentration['top_10_share'] . '% of revenue - high concentration risk',
                'priority' => 'high'
            ];
        } elseif ($concentration['top_10_share'] > 30) {
            $insights[] = [
                'type' => 'info',
                'category' => 'Concentration',
                'message' => 'Top 10 customers represent ' . $concentration['top_10_share'] . '% of revenue - moderate concentration',
                'priority' => 'medium'
            ];
        }

        // RFM segment insights
        $segments = $this->analyzeSegments($currentData);
        if ($segments['at_risk']['count'] > $currentData['active_customers'] * 0.2) {
            $insights[] = [
                'type' => 'danger',
                'category' => 'Retention',
                'message' => $segments['at_risk']['count'] . ' customers are at risk - immediate retention efforts needed',
                'priority' => 'high'
            ];
        }

        if ($segments['high_value']['count'] < $currentData['active_customers'] * 0.1) {
            $insights[] = [
                'type' => 'info',
                'category' => 'Growth',
                'message' => 'Only ' . $segments['high_value']['count'] . ' high-value customers - focus on customer development',
                'priority' => 'medium'
            ];
        }

        // Repeat customer insights
        if ($currentData['repeat_customer_rate'] < 60) {
            $insights[] = [
                'type' => 'warning',
                'category' => 'Retention',
                'message' => 'Only ' . $currentData['repeat_customer_rate'] . '% of customers are repeat buyers - focus on customer success',
                'priority' => 'medium'
            ];
        }

        return $insights;
    }

    /**
     * Calculate percentage change with proper bounds
     */
    private function calculatePercentageChange(float $oldValue, float $newValue): float
    {
        if ($oldValue == 0) {
            return $newValue > 0 ? 100 : 0; // If old value was 0, new value represents 100% growth
        }
        
        $change = (($newValue - $oldValue) / $oldValue) * 100;
        
        // Cap extreme changes to prevent unrealistic percentages
        return max(-100, min(100, $change));
    }

    /**
     * Calculate months inactive based on snapshot date
     */
    private function calculateMonthsInactive(string $lastActiveDate): int
    {
        $lastActive = Carbon::parse($lastActiveDate);
        $now = Carbon::now();
        return $lastActive->diffInMonths($now);
    }
}
