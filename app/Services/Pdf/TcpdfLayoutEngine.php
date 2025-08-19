<?php

namespace App\Services\Pdf;

use TCPDF;

class TcpdfLayoutEngine
{
    private $styling;
    private $formatter;
    
    public function __construct(PdfStylingService $styling, DataFormatter $formatter)
    {
        $this->styling = $styling;
        $this->formatter = $formatter;
    }
    
    public function createReport(array $reportData): TCPDF
    {
        $pdf = new TCPDF();
        
        // Setup page
        $pdf->SetCreator('RFM Analysis System');
        $pdf->SetAuthor('Your Company');
        $pdf->SetTitle('RFM Analysis Report - ' . $reportData['date']);
        
        // Set margins
        $pdf->SetMargins(15, 20, 15);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);
        
        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Add pages
        $this->addCoverPage($pdf, $reportData);
        $this->addExecutiveSummary($pdf, $reportData);
        $this->addCustomerSegments($pdf, $reportData);
        $this->addCustomerMovement($pdf, $reportData);
        $this->addDetailedRfmScores($pdf, $reportData);
        $this->addRankingChanges($pdf, $reportData);
        $this->addHistoricalTrends($pdf, $reportData);
        $this->addRecentlyLostCustomers($pdf, $reportData);
        $this->addRiskAssessment($pdf, $reportData);
        $this->addGrowthOpportunities($pdf, $reportData);
        
        return $pdf;
    }
    
    private function addCoverPage(TCPDF $pdf, array $data): void
    {
        $pdf->AddPage();
        
        // Title
        $this->styling->applyHeaderStyle($pdf, 'RFM Analysis Report');
        
        // Report details
        $pdf->SetFont('helvetica', '', 12);
        $pdf->SetTextColor(107, 114, 128);
        $pdf->Cell(0, 8, 'Analysis Date: ' . $this->formatter->formatDate($data['date']), 0, 1, 'L');
        $pdf->Cell(0, 8, 'Generated: ' . $this->formatter->formatDateTime(now()), 0, 1, 'L');
        $pdf->Cell(0, 8, 'Organization: ' . $data['organization'], 0, 1, 'L');
        $pdf->Cell(0, 8, 'Analysis Window: ' . $data['rfm_window'] . ' months', 0, 1, 'L');
        
        $pdf->Ln(20);
        
        // About this report
        $this->styling->applySubheaderStyle($pdf, 'About This Report');
        $this->styling->applyBodyStyle($pdf, 'This comprehensive RFM report analyzes your customer base using Recency, Frequency, and Monetary methodology. It provides actionable insights into customer behavior, revenue patterns, and business performance trends.');
    }
    
    private function addExecutiveSummary(TCPDF $pdf, array $data): void
    {
        $pdf->AddPage();
        
        $this->styling->applySubheaderStyle($pdf, 'Executive Summary');
        
        // KPI Table
        $this->addKpiTable($pdf, $data);
        
        // Key Insights
        if (!empty($data['insights'])) {
            $pdf->Ln(10);
            $this->styling->applySectionStyle($pdf, 'Key Business Insights');
            foreach ($data['insights'] as $insight) {
                $this->styling->applyBodyStyle($pdf, '• ' . $insight['message']);
            }
        }
    }
    
    private function addKpiTable(TCPDF $pdf, array $data): void
    {
        $this->styling->applyTableHeaderStyle($pdf);
        
        // Table headers
        $pdf->Cell(60, 8, 'Metric', 1, 0, 'L', true);
        $pdf->Cell(40, 8, 'Current', 1, 0, 'C', true);
        $pdf->Cell(40, 8, 'Previous', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'Change', 1, 1, 'C', true);
        
        // Table data
        $this->styling->applyTableBodyStyle($pdf);
        
        $currentPeriod = $data['current_period'] ?? [];
        $analysis = $data['analysis'] ?? [];
        
        $this->addKpiRow($pdf, 'Total Revenue', $currentPeriod['total_revenue'] ?? 0, $analysis['revenue_change'] ?? 0);
        $this->addKpiRow($pdf, 'Active Customers', $currentPeriod['active_customers'] ?? 0, $analysis['active_customers_change'] ?? 0);
        $this->addKpiRow($pdf, 'Average RFM Score', $currentPeriod['average_rfm'] ?? 0, $analysis['average_rfm_change'] ?? 0);
        $this->addKpiRow($pdf, 'Average Order Value', $currentPeriod['average_order_value'] ?? 0, $analysis['average_order_value_change'] ?? 0);
    }
    
    private function addKpiRow(TCPDF $pdf, string $label, float $current, float $change): void
    {
        $pdf->Cell(60, 7, $label, 1, 0, 'L');
        
        // Format value based on the metric type
        if (strpos($label, 'Revenue') !== false || strpos($label, 'Value') !== false) {
            $formattedValue = $this->formatter->formatCurrency($current);
        } else if (strpos($label, 'RFM') !== false) {
            $formattedValue = $this->formatter->formatRfmScore($current);
        } else {
            $formattedValue = $this->formatter->formatNumber((int)$current);
        }
        
        $pdf->Cell(40, 7, $formattedValue, 1, 0, 'R');
        $pdf->Cell(40, 7, 'N/A', 1, 0, 'R'); // Previous period data not available in current structure
        
        $changeColor = $this->formatter->getChangeColor($change);
        $pdf->SetTextColor(...$changeColor);
        $pdf->Cell(30, 7, $this->formatter->formatChange($change), 1, 1, 'R');
        $pdf->SetTextColor(0, 0, 0);
    }
    
    private function addCustomerSegments(TCPDF $pdf, array $data): void
    {
        $pdf->AddPage();
        
        $this->styling->applySubheaderStyle($pdf, 'Customer Segments');
        
        // Segment breakdown table
        $this->styling->applyTableHeaderStyle($pdf);
        $pdf->Cell(50, 8, 'Segment', 1, 0, 'L', true);
        $pdf->Cell(30, 8, 'Count', 1, 0, 'C', true);
        $pdf->Cell(40, 8, 'Avg RFM', 1, 0, 'C', true);
        $pdf->Cell(40, 8, 'Revenue %', 1, 1, 'C', true);
        
        $this->styling->applyTableBodyStyle($pdf);
        
        $segments = $data['segments'] ?? [];
        foreach ($segments as $key => $segment) {
            $segmentName = ucwords(str_replace('_', ' ', $key));
            $pdf->Cell(50, 7, $segmentName, 1, 0, 'L');
            $pdf->Cell(30, 7, $this->formatter->formatNumber($segment['count']), 1, 0, 'C');
            $pdf->Cell(40, 7, $this->formatter->formatRfmScore($segment['avg_rfm']), 1, 0, 'C');
            $pdf->Cell(40, 7, $this->formatter->formatPercentage($segment['revenue_share'] ?? 0), 1, 1, 'C');
        }
        
        // Add concentration analysis
        $pdf->Ln(10);
        $this->styling->applySectionStyle($pdf, 'Revenue Concentration');
        
        $concentration = $data['concentration'] ?? [];
        if (!empty($concentration)) {
            $this->styling->applyBodyStyle($pdf, 'Top 10 customers represent ' . $this->formatter->formatPercentage($concentration['top_10_percentage'] ?? 0) . ' of total revenue.');
            $this->styling->applyBodyStyle($pdf, 'Top 50 customers represent ' . $this->formatter->formatPercentage($concentration['top_50_percentage'] ?? 0) . ' of total revenue.');
            $this->styling->applyBodyStyle($pdf, 'Gini coefficient: ' . number_format($concentration['gini_coefficient'] ?? 0, 3));
        }
    }
    
    private function addCustomerMovement(TCPDF $pdf, array $data): void
    {
        $pdf->AddPage();
        
        $this->styling->applySubheaderStyle($pdf, 'Customer Movement');
        
        $movement = $data['movement'] ?? [];
        
        // Movement summary table
        $this->styling->applyTableHeaderStyle($pdf);
        $pdf->Cell(60, 8, 'Movement Type', 1, 0, 'L', true);
        $pdf->Cell(40, 8, 'Count', 1, 0, 'C', true);
        $pdf->Cell(40, 8, 'Percentage', 1, 1, 'C', true);
        
        $this->styling->applyTableBodyStyle($pdf);
        
        $movementTypes = [
            'retained_customers' => 'Retained',
            'new_customers' => 'New',
            'returned_customers' => 'Returned',
            'lost_customers' => 'Lost'
        ];
        
        foreach ($movementTypes as $key => $label) {
            $count = $movement[$key] ?? 0;
            $totalCustomers = array_sum(array_map(fn($k) => $movement[$k] ?? 0, array_keys($movementTypes)));
            $percentage = $totalCustomers > 0 ? ($count / $totalCustomers) * 100 : 0;
            
            $pdf->Cell(60, 7, $label, 1, 0, 'L');
            $pdf->Cell(40, 7, $this->formatter->formatNumber($count), 1, 0, 'C');
            $pdf->Cell(40, 7, $this->formatter->formatPercentage($percentage), 1, 1, 'C');
        }
    }
    
    private function addRiskAssessment(TCPDF $pdf, array $data): void
    {
        $pdf->AddPage();
        
        $this->styling->applySubheaderStyle($pdf, 'Risk Assessment');
        
        $risks = $data['risk_analysis'] ?? [];
        
        foreach ($risks as $risk) {
            $this->styling->applySectionStyle($pdf, $risk['title']);
            $this->styling->applyBodyStyle($pdf, $risk['description']);
            $this->styling->applyBodyStyle($pdf, 'Impact: ' . $risk['impact']);
            $this->styling->applyBodyStyle($pdf, 'Recommendation: ' . $risk['recommendation']);
            $pdf->Ln(5);
        }
    }
    
    private function addGrowthOpportunities(TCPDF $pdf, array $data): void
    {
        $pdf->AddPage();
        
        $this->styling->applySubheaderStyle($pdf, 'Growth Opportunities');
        
        $opportunities = $data['opportunities'] ?? [];
        
        foreach ($opportunities as $opportunity) {
            $this->styling->applySectionStyle($pdf, $opportunity['title']);
            $this->styling->applyBodyStyle($pdf, $opportunity['description']);
            $this->styling->applyBodyStyle($pdf, 'Potential Impact: ' . $opportunity['potential_impact']);
            
            if (!empty($opportunity['action_items'])) {
                $this->styling->applyBodyStyle($pdf, 'Action Items:');
                foreach ($opportunity['action_items'] as $item) {
                    $this->styling->applyBodyStyle($pdf, '• ' . $item);
                }
            }
            $pdf->Ln(5);
        }
    }
    
    private function addDetailedRfmScores(TCPDF $pdf, array $data): void
    {
        $pdf->AddPage();
        
        $this->styling->applySubheaderStyle($pdf, 'Detailed RFM Scores');
        $this->styling->applyBodyStyle($pdf, 'Individual customer RFM analysis');
        
        // Get RFM reports from current period data
        $currentPeriod = $data['current_period'] ?? [];
        $rfmReports = $currentPeriod['rfm_reports'] ?? collect();
        
        // Sort by RFM score descending and take top 10
        $topCustomers = $rfmReports->sortByDesc('rfm_score')->take(10);
        
        if ($topCustomers->isNotEmpty()) {
            // Table headers
            $this->styling->applyTableHeaderStyle($pdf);
            $pdf->Cell(40, 8, 'Client', 1, 0, 'L', true);
            $pdf->Cell(20, 8, 'R', 1, 0, 'C', true);
            $pdf->Cell(20, 8, 'F', 1, 0, 'C', true);
            $pdf->Cell(20, 8, 'M', 1, 0, 'C', true);
            $pdf->Cell(20, 8, 'RFM', 1, 1, 'C', true);
            
            // Table data
            $this->styling->applyTableBodyStyle($pdf);
            
            foreach ($topCustomers as $customer) {
                $pdf->Cell(40, 7, substr($customer->client_name, 0, 20), 1, 0, 'L');
                $pdf->Cell(20, 7, number_format($customer->recency_score, 1), 1, 0, 'C');
                $pdf->Cell(20, 7, number_format($customer->frequency_score, 0), 1, 0, 'C');
                $pdf->Cell(20, 7, number_format($customer->monetary_score, 1), 1, 0, 'C');
                $pdf->Cell(20, 7, number_format($customer->rfm_score, 1), 1, 1, 'C');
            }
            
            $pdf->Ln(5);
            $this->styling->applyBodyStyle($pdf, 'Showing top 10 of ' . $rfmReports->count() . ' customers');
        }
    }
    
    private function addRankingChanges(TCPDF $pdf, array $data): void
    {
        $pdf->AddPage();
        
        $this->styling->applySubheaderStyle($pdf, 'Customer Ranking Changes');
        $this->styling->applyBodyStyle($pdf, 'Track how customer positions have changed over time');
        
        $customerMovementDetails = $data['customer_movement_details'] ?? [];
        
        // Top Ranking Improvers
        $pdf->Ln(5);
        $this->styling->applySectionStyle($pdf, 'Top Ranking Improvers');
        
        $rankingChanges = $customerMovementDetails['ranking_changes'] ?? [];
        $improvers = array_filter($rankingChanges, function($change) {
            return is_numeric($change['rank_change']) && $change['rank_change'] > 0 || $change['rank_change'] === 'New Active';
        });
        
        if (!empty($improvers)) {
            foreach (array_slice($improvers, 0, 3) as $improver) {
                $changeText = $improver['rank_change'] === 'New Active' ? 'Became Active' : 'RFM: ' . number_format($improver['previous_rfm'], 1) . ' → ' . number_format($improver['current_rfm'], 1);
                $this->styling->applyBodyStyle($pdf, $improver['client_name'] . ' - ' . $changeText);
            }
        } else {
            $this->styling->applyBodyStyle($pdf, 'No significant improvements this period');
        }
        
        // Top Ranking Decliners
        $pdf->Ln(5);
        $this->styling->applySectionStyle($pdf, 'Top Ranking Decliners');
        
        $decliners = array_filter($rankingChanges, function($change) {
            return is_numeric($change['rank_change']) && $change['rank_change'] < 0;
        });
        
        if (!empty($decliners)) {
            foreach (array_slice($decliners, 0, 5) as $decliner) {
                $changeText = 'RFM: ' . number_format($decliner['previous_rfm'], 1) . ' → ' . number_format($decliner['current_rfm'], 1);
                $this->styling->applyBodyStyle($pdf, $decliner['client_name'] . ' - ' . $changeText . ' (' . number_format($decliner['rank_change'], 1) . ')');
            }
        } else {
            $this->styling->applyBodyStyle($pdf, 'No significant declines this period');
        }
    }
    
    private function addHistoricalTrends(TCPDF $pdf, array $data): void
    {
        $pdf->AddPage();
        
        $this->styling->applySubheaderStyle($pdf, 'Historical Trends');
        $this->styling->applyBodyStyle($pdf, 'Track performance over the last 6 periods');
        
        $historicalTrends = $data['historical_trends'] ?? [];
        
        if (!empty($historicalTrends)) {
            // Table headers
            $this->styling->applyTableHeaderStyle($pdf);
            $pdf->Cell(35, 8, 'Period', 1, 0, 'L', true);
            $pdf->Cell(30, 8, 'Customers', 1, 0, 'C', true);
            $pdf->Cell(25, 8, 'Avg RFM', 1, 0, 'C', true);
            $pdf->Cell(25, 8, 'High Value', 1, 0, 'C', true);
            $pdf->Cell(25, 8, 'At Risk', 1, 1, 'C', true);
            
            // Table data
            $this->styling->applyTableBodyStyle($pdf);
            
            foreach ($historicalTrends as $trend) {
                $date = $trend['formatted_date'] ?? $trend['date'] ?? 'Unknown';
                $pdf->Cell(35, 7, $date, 1, 0, 'L');
                $pdf->Cell(30, 7, $this->formatter->formatNumber($trend['total_customers']), 1, 0, 'C');
                $pdf->Cell(25, 7, number_format($trend['average_rfm'], 2), 1, 0, 'C');
                $pdf->Cell(25, 7, $this->formatter->formatNumber($trend['high_value_customers']), 1, 0, 'C');
                $pdf->Cell(25, 7, $this->formatter->formatNumber($trend['at_risk_customers']), 1, 1, 'C');
            }
        }
    }
    
    private function addRecentlyLostCustomers(TCPDF $pdf, array $data): void
    {
        $pdf->AddPage();
        
        $this->styling->applySubheaderStyle($pdf, 'Recently Lost Customers');
        $this->styling->applyBodyStyle($pdf, 'Customers who were active but became inactive');
        
        $customerMovementDetails = $data['customer_movement_details'] ?? [];
        $recentlyLost = $customerMovementDetails['recently_lost_customers'] ?? [];
        
        if (!empty($recentlyLost)) {
            // Table headers
            $this->styling->applyTableHeaderStyle($pdf);
            $pdf->Cell(50, 8, 'Customer', 1, 0, 'L', true);
            $pdf->Cell(30, 8, 'Previous RFM', 1, 0, 'C', true);
            $pdf->Cell(40, 8, 'Inactive For', 1, 1, 'C', true);
            
            // Table data
            $this->styling->applyTableBodyStyle($pdf);
            
            foreach (array_slice($recentlyLost, 0, 10) as $customer) {
                $pdf->Cell(50, 7, substr($customer['client_name'], 0, 25), 1, 0, 'L');
                $pdf->Cell(30, 7, number_format($customer['previous_rfm'], 2), 1, 0, 'C');
                $pdf->Cell(40, 7, $customer['months_inactive'] . ' month(s)', 1, 1, 'C');
            }
            
            $pdf->Ln(5);
            $this->styling->applySectionStyle($pdf, 'Re-engagement Opportunity');
            $this->styling->applyBodyStyle($pdf, count($recentlyLost) . ' customers recently became inactive. These are prime candidates for re-engagement campaigns as they were previously active customers.');
        } else {
            $this->styling->applyBodyStyle($pdf, 'No recently lost customers identified.');
        }
    }
}
