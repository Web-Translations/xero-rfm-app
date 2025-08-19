<?php

namespace App\Services\Pdf;

class DataFormatter
{
    public function formatCurrency(float $amount): string
    {
        return '£' . number_format($amount, 0);
    }
    
    public function formatPercentage(float $percentage): string
    {
        return number_format($percentage, 1) . '%';
    }
    
    public function formatChange(float $change): string
    {
        $sign = $change >= 0 ? '+' : '';
        return $sign . number_format($change, 1) . '%';
    }
    
    public function formatRfmScore(float $score): string
    {
        return number_format($score, 2);
    }
    
    public function formatNumber(int $number): string
    {
        return number_format($number);
    }
    
    public function formatDate(string $date): string
    {
        return \Carbon\Carbon::parse($date)->format('F j, Y');
    }
    
    public function formatDateTime(string $datetime): string
    {
        return \Carbon\Carbon::parse($datetime)->format('M j, Y \a\t g:i A');
    }
    
    public function getChangeSymbol(float $change): string
    {
        if ($change > 0) return '▲';
        if ($change < 0) return '▼';
        return '─';
    }
    
    public function getChangeColor(float $change): array
    {
        if ($change > 0) return [16, 185, 129]; // Green
        if ($change < 0) return [239, 68, 68];  // Red
        return [107, 114, 128]; // Gray
    }
}
