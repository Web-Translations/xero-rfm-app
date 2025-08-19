<?php

namespace App\Services\Pdf;

use TCPDF;

class PdfStylingService
{
    private $colors = [
        'primary' => [59, 130, 246],    // Blue
        'success' => [16, 185, 129],    // Green
        'warning' => [245, 158, 11],    // Orange
        'danger' => [239, 68, 68],      // Red
        'gray' => [107, 114, 128],      // Gray
        'light_gray' => [243, 244, 246], // Light gray
        'dark_gray' => [55, 65, 81],    // Dark gray
    ];
    
    private $fonts = [
        'header' => 'helvetica',
        'subheader' => 'helvetica',
        'body' => 'helvetica',
        'mono' => 'courier'
    ];
    
    public function applyHeaderStyle(TCPDF $pdf, string $text): void
    {
        $pdf->SetFont($this->fonts['header'], 'B', 20);
        $pdf->SetTextColor(...$this->colors['primary']);
        $pdf->Cell(0, 15, $text, 0, 1, 'L');
        $pdf->Ln(5);
    }
    
    public function applySubheaderStyle(TCPDF $pdf, string $text): void
    {
        $pdf->SetFont($this->fonts['subheader'], 'B', 16);
        $pdf->SetTextColor(...$this->colors['dark_gray']);
        $pdf->Cell(0, 12, $text, 0, 1, 'L');
        $pdf->Ln(3);
    }
    
    public function applySectionStyle(TCPDF $pdf, string $text): void
    {
        $pdf->SetFont($this->fonts['subheader'], 'B', 14);
        $pdf->SetTextColor(...$this->colors['gray']);
        $pdf->Cell(0, 10, $text, 0, 1, 'L');
        $pdf->Ln(2);
    }
    
    public function applyBodyStyle(TCPDF $pdf, string $text): void
    {
        $pdf->SetFont($this->fonts['body'], '', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(0, 6, $text, 0, 'L');
        $pdf->Ln(3);
    }
    
    public function applyTableHeaderStyle(TCPDF $pdf): void
    {
        $pdf->SetFont($this->fonts['body'], 'B', 10);
        $pdf->SetFillColor(...$this->colors['light_gray']);
        $pdf->SetTextColor(...$this->colors['dark_gray']);
    }
    
    public function applyTableBodyStyle(TCPDF $pdf): void
    {
        $pdf->SetFont($this->fonts['body'], '', 9);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);
    }
    
    public function getColors(): array
    {
        return $this->colors;
    }
}
