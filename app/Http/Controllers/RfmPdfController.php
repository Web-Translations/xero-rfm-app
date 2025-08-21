<?php

namespace App\Http\Controllers;

use App\Services\Pdf\RfmPdfService;
use App\Services\Rfm\RfmTools;
use App\Models\RfmConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RfmPdfController extends Controller
{
    private RfmPdfService $pdfService;
    private RfmTools $rfmTools;
    
    public function __construct(RfmPdfService $pdfService, RfmTools $rfmTools)
    {
        $this->pdfService = $pdfService;
        $this->rfmTools = $rfmTools;
    }
    
    public function download(Request $request)
    {
        try {
            // Get user and active connection
            $user = Auth::user();
            $activeConnection = $user->xeroConnections()->where('is_active', true)->first();
            
            if (!$activeConnection) {
                return response()->json(['error' => 'No active Xero connection found.'], 400);
            }
            
            // Get report parameters from request
            $snapshotDate = $request->input('snapshot_date', now()->format('Y-m-01'));
            $rfmWindow = $request->input('rfm_window', 12);
            $comparisonPeriod = $request->input('comparison_period', 'monthly');
            
            // Get RFM configuration
            $config = RfmConfiguration::getOrCreateDefault($user->id, $activeConnection->tenant_id);
            
            // Calculate comparison date
            $comparisonSnapshotDate = $this->calculateComparisonDate($snapshotDate, $comparisonPeriod);
            
            // Generate comprehensive report data
            $reportData = $this->rfmTools->computeKpis(
                $user->id,
                $activeConnection->tenant_id,
                $snapshotDate,
                $comparisonSnapshotDate,
                $config
            );
            

            
            // Add metadata for PDF
            $reportData['date'] = $snapshotDate;
            $reportData['rfm_window'] = $rfmWindow;
            $reportData['comparison_period'] = $comparisonPeriod;
            
            // Generate beautiful PDF
            $pdfPath = $this->pdfService->generateReport($reportData, $activeConnection->org_name);
            
            // Verify PDF was created
            if (!file_exists($pdfPath)) {
                return response()->json(['error' => 'Failed to generate PDF report.'], 500);
            }
            
            // Create download filename
            $filename = $this->generateFilename($activeConnection->org_name, $snapshotDate);
            
            // Return PDF download
            return response()->download($pdfPath, $filename, [
                'Content-Type' => 'application/pdf',
                'Cache-Control' => 'no-cache, must-revalidate',
                'Pragma' => 'no-cache'
            ])->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            \Log::error('PDF Generation Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to generate PDF report. Please try again.'
            ], 500);
        }
    }
    
    public function generateFromBuilder(Request $request)
    {
        try {
            // Validate the incoming request
            $validated = $request->validate([
                'snapshot_date' => 'required|date',
                'rfm_window' => 'required|integer|min:1|max:60',
                'comparison_period' => 'required|in:monthly,quarterly,yearly'
            ]);
            
            // Get user and active connection
            $user = Auth::user();
            $activeConnection = $user->xeroConnections()->where('is_active', true)->first();
            
            if (!$activeConnection) {
                return back()->with('error', 'No active Xero connection found.');
            }
            
            // Get RFM configuration
            $config = RfmConfiguration::getOrCreateDefault($user->id, $activeConnection->tenant_id);
            
            // Calculate comparison date
            $comparisonSnapshotDate = $this->calculateComparisonDate(
                $validated['snapshot_date'], 
                $validated['comparison_period']
            );
            
            // Generate comprehensive report data
            $reportData = $this->rfmTools->computeKpis(
                $user->id,
                $activeConnection->tenant_id,
                $validated['snapshot_date'],
                $comparisonSnapshotDate,
                $config
            );
            
            // Add metadata for PDF
            $reportData['date'] = $validated['snapshot_date'];
            $reportData['rfm_window'] = $validated['rfm_window'];
            $reportData['comparison_period'] = $validated['comparison_period'];
            
            // Generate beautiful PDF
            $pdfPath = $this->pdfService->generateReport($reportData, $activeConnection->org_name);
            
            // Verify PDF was created
            if (!file_exists($pdfPath)) {
                return back()->with('error', 'Failed to generate PDF report. Please try again.');
            }
            
            // Create download filename
            $filename = $this->generateFilename($activeConnection->org_name, $validated['snapshot_date']);
            
            // Return PDF download
            return response()->download($pdfPath, $filename, [
                'Content-Type' => 'application/pdf',
                'Cache-Control' => 'no-cache, must-revalidate',
                'Pragma' => 'no-cache'
            ])->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            \Log::error('PDF Generation Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate PDF report. Please try again.');
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
