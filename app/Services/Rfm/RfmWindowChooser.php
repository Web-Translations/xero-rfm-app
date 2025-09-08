<?php

namespace App\Services\Rfm;

use App\Models\ExcludedInvoice;
use App\Models\XeroInvoice;
use Illuminate\Support\Carbon;

class RfmWindowChooser
{
    /**
     * Decide final analysis window (12/24/36) based on max Frequency observed.
     * Returns [ 'final_window' => int, 'observations' => array<array{window:int,maxF:int}> ]
     */
    public function chooseFinalWindow(int $userId, string $tenantId, int $threshold = 5): array
    {
        $candidates = [12, 24, 36];
        $excludedIds = ExcludedInvoice::getExcludedInvoiceIds($userId, $tenantId);
        $observations = [];

        foreach ($candidates as $months) {
            $start = Carbon::now()->subMonths($months)->startOfDay()->toDateString();
            $end = Carbon::now()->toDateString();

            $freqPerClient = XeroInvoice::query()
                ->selectRaw('contact_id, COUNT(*) as c')
                ->where('user_id', $userId)
                ->where('tenant_id', $tenantId)
                ->whereBetween('date', [$start, $end])
                ->when(!empty($excludedIds), function ($q) use ($excludedIds) {
                    $q->whereNotIn('invoice_id', $excludedIds);
                })
                ->groupBy('contact_id')
                ->pluck('c')
                ->map(function ($count) {
                    return min(10, (int) $count);
                });

            $maxF = (int) ($freqPerClient->max() ?? 0);
            $observations[] = ['window' => $months, 'maxF' => $maxF];
            if ($maxF >= $threshold) {
                return [
                    'final_window' => $months,
                    'observations' => $observations,
                    'fallback' => false,
                ];
            }
        }

        // If none met threshold, revert to default 12 months and flag fallback
        return [
            'final_window' => 12,
            'observations' => $observations,
            'fallback' => true,
        ];
    }
}


