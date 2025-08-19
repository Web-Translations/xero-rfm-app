<?php

namespace App\Services\Pdf;

class RfmPdfGenerator
{
    private $layoutEngine;
    
    public function __construct(TcpdfLayoutEngine $layoutEngine)
    {
        $this->layoutEngine = $layoutEngine;
    }
    
    public function generate(array $reportData): string
    {
        try {
            // Ensure the PDF directory exists
            $pdfDir = storage_path('app/pdf');
            if (!file_exists($pdfDir)) {
                mkdir($pdfDir, 0755, true);
            }
            
            // 1. Create TCPDF document
            $pdf = $this->layoutEngine->createReport($reportData);
            
            // 2. Generate unique filename
            $filename = 'rfm_report_' . date('Y-m-d_H-i-s') . '.pdf';
            $filepath = $pdfDir . '/' . $filename;
            
            // 3. Save PDF
            $pdf->Output($filepath, 'F');
            
            // 4. Verify file was created
            if (!file_exists($filepath)) {
                throw new \Exception('PDF file was not created after Output() call');
            }
            
            return $filepath;
            
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
