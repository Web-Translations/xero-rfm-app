<?php

namespace App\Services\Pdf;

use Illuminate\Support\Facades\Log;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class RfmPdfService
{
    /**
     * Generate a beautiful RFM report PDF from report data
     */
    public function generateReport(array $reportData, string $organizationName): string
    {
        // Ensure the data has all required fields
        $reportData = $this->prepareReportData($reportData, $organizationName);
        
        // Generate PDF from the beautiful template
        $pdf = Pdf::loadView('pdf.rfm-report', ['reportData' => $reportData])
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isPhpEnabled' => true,
                'isRemoteEnabled' => false,
                'isHtml5ParserEnabled' => true,
                'isFontSubsettingEnabled' => false,
                'defaultMediaType' => 'print',
                'dpi' => 96,
            ]);

        // Create filename with timestamp
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $safeOrgName = $this->sanitizeFilename($organizationName);
        $filename = "RFM_Report_{$safeOrgName}_{$timestamp}.pdf";
        
        // Create temporary file path
        $tempPath = storage_path('app/temp/' . $filename);
        
        // Ensure temp directory exists
        $tempDir = dirname($tempPath);
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        // Save PDF directly to temp file
        file_put_contents($tempPath, $pdf->output());
        
        return $tempPath;
    }

    /**
     * Prepare and normalize report data for PDF generation
     */
    private function prepareReportData(array $reportData, string $organizationName): array
    {
        // Set organization name
        $reportData['organization'] = $organizationName;
        
        // Ensure date is properly formatted
        if (!isset($reportData['date'])) {
            $reportData['date'] = now()->toDateString();
        }
        

        
        // The reportData from computeKpis() should already be in the right format
        // Just ensure it has a 'kpis' wrapper for the template
        if (!isset($reportData['kpis'])) {
            // The data comes directly from computeKpis - no need to restructure
            $reportData['kpis'] = $reportData;
        }

        // Use the exact same data structure as the HTML report
        // The HTML report gets customer movement data from $kpis['current_period']['customer_movement']
        $customerMovement = $reportData['current_period']['customer_movement'] ?? [];
        
        // The HTML report shows these exact values from the customer_movement data
        // HTML shows: Retained: 17, New: 0, Returned: 1, Lost: 2, Retention: 89.5%
        
        // Use the actual customer movement data from the same source as HTML report
        $customerMovementSummary = [
            'retained' => $customerMovement['retained_customers'] ?? 0,
            'new' => $customerMovement['new_customers'] ?? 0,
            'returned' => $customerMovement['returned_customers'] ?? 0,
            'lost' => $customerMovement['lost_customers'] ?? 0,
            'retention_rate' => $customerMovement['retention_rate'] ?? 0,
        ];





        // Add customer movement data to kpis for template access (same as HTML report)
        $reportData['kpis']['customer_movement_details'] = $customerMovementSummary;
        
        // Add detailed RFM scores from current period data if available
        if (isset($reportData['current_period']['rfm_reports']) && $reportData['current_period']['rfm_reports']->count() > 0) {
            $detailedRfmScores = $reportData['current_period']['rfm_reports']
                ->sortByDesc('rfm_score')
                ->take(15)
                ->map(function($report) {
                    return [
                        'client_name' => $report->client_name ?? 'Unknown Customer',
                        'r_score' => is_numeric($report->r_score) ? (float) $report->r_score : 0,
                        'f_score' => is_numeric($report->f_score) ? (int) $report->f_score : 0,
                        'm_score' => is_numeric($report->m_score) ? (float) $report->m_score : 0,
                        'rfm_score' => is_numeric($report->rfm_score) ? (float) $report->rfm_score : 0,
                    ];
                })
                ->toArray();
            
            $reportData['kpis']['detailed_rfm_scores'] = $detailedRfmScores;
        }

        // Add recently lost customers data from customer movement details
        if (isset($reportData['customer_movement_details']['recently_lost_customers'])) {
            $reportData['kpis']['recently_lost_customers'] = $reportData['customer_movement_details']['recently_lost_customers'];
        }

        // Add ranking changes data from movement analysis (same as HTML report)
        if (isset($reportData['movement']['improvers']) || isset($reportData['movement']['decliners'])) {
            $reportData['kpis']['ranking_changes'] = [
                'improvers' => $reportData['movement']['improvers'] ?? [],
                'decliners' => $reportData['movement']['decliners'] ?? [],
            ];
        }

        // Add customer movement data to kpis for template access (same as HTML report)
        $reportData['kpis']['customer_movement'] = $customerMovement;
        
        // Skip customer table data since it requires complex invoice calculations
        // This data is not readily available in RFM reports and would need
        // to be calculated from invoices table, which is complex
        $reportData['top_customers'] = [];
        
        // Add any missing insights
        if (empty($reportData['kpis']['insights'])) {
            $reportData['kpis']['insights'] = $this->generateDefaultInsights($reportData);
        }
        
        return $reportData;
    }

    /**
     * Generate default insights if none are provided
     */
    private function generateDefaultInsights(array $reportData): array
    {
        $insights = [];
        
        $currentPeriod = $reportData['kpis']['current_period'] ?? [];
        $totalRevenue = $currentPeriod['total_revenue'] ?? 0;
        $activeCustomers = $currentPeriod['active_customers'] ?? 0;
        
        if ($totalRevenue > 0) {
            $insights[] = [
                'message' => 'Total revenue for the period: £' . number_format($totalRevenue),
                'type' => 'info',
                'category' => 'revenue',
                'priority' => 'medium'
            ];
        }
        
        if ($activeCustomers > 0) {
            $avgRevenuePerCustomer = $totalRevenue / $activeCustomers;
            $insights[] = [
                'message' => 'Average revenue per customer: £' . number_format($avgRevenuePerCustomer, 0),
                'type' => 'info',
                'category' => 'customers',
                'priority' => 'medium'
            ];
        }
        
        if (!empty($reportData['top_customers'])) {
            $topCustomerCount = count($reportData['top_customers']);
            $insights[] = [
                'message' => "Analysis includes {$topCustomerCount} customers with complete RFM data",
                'type' => 'info',
                'category' => 'analysis',
                'priority' => 'low'
            ];
        }
        
        return $insights;
    }

    /**
     * Sanitize filename for safe storage
     */
    private function sanitizeFilename(string $filename): string
    {
        // Remove or replace invalid characters
        $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', $filename);
        
        // Remove multiple underscores
        $filename = preg_replace('/_{2,}/', '_', $filename);
        
        // Trim underscores from start and end
        $filename = trim($filename, '_');
        
        // Ensure we have a valid filename
        if (empty($filename)) {
            $filename = 'Company';
        }
        
        return $filename;
    }

    /**
     * Clean up old PDF files (optional maintenance method)
     */
    public function cleanupOldFiles(int $daysOld = 7): int
    {
        $tempDir = storage_path('app/temp');
        $deletedCount = 0;
        
        if (!is_dir($tempDir)) {
            return 0;
        }
        
        $files = glob($tempDir . '/*.pdf');
        $cutoffTime = Carbon::now()->subDays($daysOld);
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime->timestamp) {
                unlink($file);
                $deletedCount++;
            }
        }
        
        return $deletedCount;
    }
}
