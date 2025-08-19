<?php

namespace App\Http\Controllers;

use App\Services\Pdf\RfmPdfGenerator;
use App\Services\Rfm\RfmTools;
use App\Models\RfmConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RfmPdfController extends Controller
{
    private $pdfGenerator;
    private $rfmTools;
    
    public function __construct(RfmPdfGenerator $pdfGenerator, RfmTools $rfmTools)
    {
        $this->pdfGenerator = $pdfGenerator;
        $this->rfmTools = $rfmTools;
    }
    
    public function download(Request $request, $reportId = null)
    {
        try {
            // Step 1: Get user and connection
            $user = Auth::user();
            $activeConnection = $user->xeroConnections()->where('is_active', true)->first();
            
            if (!$activeConnection) {
                return response('Error: No active Xero connection found.', 400);
            }
            
            // Step 2: Get RFM configuration
            $config = RfmConfiguration::getOrCreateDefault($user->id, $activeConnection->tenant_id);
            
            // Step 3: Get report parameters
            $snapshotDate = $request->input('snapshot_date', now()->format('Y-m-01'));
            $rfmWindow = $request->input('rfm_window', 12);
            $comparisonPeriod = $request->input('comparison_period', 'monthly');
            
            // Step 4: Calculate comparison date
            $comparisonSnapshotDate = $this->calculateComparisonDate($snapshotDate, $comparisonPeriod);
            
            // Step 5: Generate report data
            $reportData = $this->rfmTools->computeKpis(
                $user->id,
                $activeConnection->tenant_id,
                $snapshotDate,
                $comparisonSnapshotDate,
                $config
            );
            
            // Step 6: Add metadata
            $reportData['date'] = $snapshotDate;
            $reportData['organization'] = $activeConnection->org_name;
            $reportData['rfm_window'] = $rfmWindow;
            $reportData['comparison_period'] = $comparisonPeriod;
            
            // Step 7: Generate PDF
            $pdfPath = $this->pdfGenerator->generate($reportData);
            
            // Step 8: Check if PDF was created
            if (!file_exists($pdfPath)) {
                return response('Error: PDF file was not created at: ' . $pdfPath, 500);
            }
            
            // Step 9: Create filename
            $filename = $this->generateFilename($activeConnection->org_name, $snapshotDate);
            
            // Step 10: Get file size
            $fileSize = filesize($pdfPath);
            
            // Step 11: Return download
            return response()->download($pdfPath, $filename, [
                'Content-Type' => 'application/pdf',
                'Content-Length' => $fileSize,
                'Cache-Control' => 'no-cache, must-revalidate',
                'Pragma' => 'no-cache',
                'X-Debug-PDF-Path' => $pdfPath,
                'X-Debug-File-Size' => $fileSize
            ]);
            
        } catch (\Exception $e) {
            return response('Error generating PDF: ' . $e->getMessage() . ' at line ' . $e->getLine(), 500);
        }
    }
    
    public function generateFromBuilder(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'snapshot_date' => 'required|date',
                'rfm_window' => 'required|integer|min:1|max:60',
                'comparison_period' => 'required|in:monthly,quarterly,yearly'
            ]);
            
            // Get the current user and active connection
            $user = Auth::user();
            $activeConnection = $user->xeroConnections()->where('is_active', true)->first();
            
            if (!$activeConnection) {
                return back()->with('error', 'No active Xero connection found.');
            }
            
            // Get RFM configuration
            $config = RfmConfiguration::getOrCreateDefault($user->id, $activeConnection->tenant_id);
            
            // Calculate comparison date based on period
            $comparisonSnapshotDate = $this->calculateComparisonDate($request->snapshot_date, $request->comparison_period);
            
            // Generate the report data
            $reportData = $this->rfmTools->computeKpis(
                $user->id,
                $activeConnection->tenant_id,
                $request->snapshot_date,
                $comparisonSnapshotDate,
                $config
            );
            
            // Add additional metadata for PDF
            $reportData['date'] = $request->snapshot_date;
            $reportData['organization'] = $activeConnection->org_name;
            $reportData['rfm_window'] = $request->rfm_window;
            $reportData['comparison_period'] = $request->comparison_period;
            
            // Generate PDF
            $pdfPath = $this->pdfGenerator->generate($reportData);
            
            // Check if PDF was actually created
            if (!file_exists($pdfPath)) {
                throw new \Exception('PDF file was not created at: ' . $pdfPath);
            }
            
            // Create a descriptive filename
            $filename = $this->generateFilename($activeConnection->org_name, $request->snapshot_date);
            
            // Add debugging info to the response headers
            $fileSize = filesize($pdfPath);
            
            // Return PDF download with custom filename and proper headers
            return response()->download($pdfPath, $filename, [
                'Content-Type' => 'application/pdf',
                'Content-Length' => $fileSize,
                'Cache-Control' => 'no-cache, must-revalidate',
                'Pragma' => 'no-cache',
                'X-Debug-PDF-Path' => $pdfPath,
                'X-Debug-File-Size' => $fileSize
            ]);
            
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }
    
    private function calculateComparisonDate(string $currentDate, string $period): ?string
    {
        $date = \Carbon\Carbon::parse($currentDate);
        
        return match($period) {
            'monthly' => $date->copy()->subMonth()->toDateString(),
            'quarterly' => $date->copy()->subQuarter()->toDateString(),
            'yearly' => $date->copy()->subYear()->toDateString(),
            'custom' => null, // Will be handled separately
            default => $date->copy()->subMonth()->toDateString(),
        };
    }
    
    private function generateFilename(string $orgName, string $date): string
    {
        // Clean organization name for filename
        $cleanOrgName = preg_replace('/[^a-zA-Z0-9\s-]/', '', $orgName);
        $cleanOrgName = str_replace([' ', '/', '\\'], '_', $cleanOrgName);
        $cleanOrgName = trim($cleanOrgName, '_');
        
        // Format date
        $formattedDate = \Carbon\Carbon::parse($date)->format('Y-m-d');
        
        return sprintf('RFM_Report_%s_%s.pdf', $cleanOrgName, $formattedDate);
    }
}
