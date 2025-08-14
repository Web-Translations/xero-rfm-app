<?php

namespace App\Services\Rfm;

use App\Models\Client;
use App\Models\XeroInvoice;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RfmCalculator
{
    /**
     * Compute RFM scores for all clients using a rolling 12-month window.
     * Stores snapshot with timestamp for historical analysis.
     */
    public function computeSnapshot(int $userId, Carbon $snapshotDate = null): array
    {
        $snapshotDate = $snapshotDate ?? Carbon::now()->startOfDay();
        $windowStart = (clone $snapshotDate)->subMonths(12)->startOfDay();
        
        // Aggregate invoice data for the rolling 12-month window
        // Include both ACCREC (sales) and ACCPAY (bills) for complete RFM analysis
        $aggregates = XeroInvoice::query()
            ->select([
                'contact_id',
                DB::raw('COUNT(*) as txn_count'),
                DB::raw('COALESCE(SUM(total), 0) as monetary_sum'),
                DB::raw('MAX(date) as last_txn_date'),
            ])
            ->where('user_id', $userId)
            ->whereIn('type', ['ACCREC', 'ACCPAY']) // Include both sales and bills
            ->where('date', '>=', $windowStart->toDateString())
            ->where('date', '<=', $snapshotDate->toDateString())
            ->groupBy('contact_id')
            ->get()
            ->keyBy('contact_id');

        if ($aggregates->isEmpty()) {
            return [
                'snapshot_date' => $snapshotDate->toDateString(),
                'window_start' => $windowStart->toDateString(),
                'computed' => 0,
            ];
        }



        // Get all monetary values for min-max scaling
        $monetaries = $aggregates->pluck('monetary_sum')->map(fn($v) => (float) $v)->values();

        // Get clients
        $clients = Client::query()
            ->where('user_id', $userId)
            ->whereIn('contact_id', $aggregates->keys()->all())
            ->get()
            ->keyBy('contact_id');

        // Compute and store RFM scores
        $computedCount = 0;

        DB::transaction(function () use (
            $clients,
            $aggregates,
            $monetaries,
            $snapshotDate,
            $userId,
            &$computedCount
        ) {
            foreach ($aggregates as $contactId => $row) {
                $client = $clients->get($contactId);
                if (!$client) {
                    // Create client shell if missing
                    $client = Client::create([
                        'user_id' => $userId,
                        'contact_id' => $contactId,
                        'name' => 'Unknown',
                    ]);
                }

                // Calculate RFM scores according to the specified methodology
                $monthsSinceLast = max(0, Carbon::parse($row->last_txn_date)->diffInMonths($snapshotDate));
                
                // R: 10 - months since last transaction (minimum 0)
                $rScore = max(0, 10 - $monthsSinceLast);
                
                // F: Number of invoices in past 12 months (capped at 10)
                $fScore = min(10, (int) $row->txn_count);
                
                // M: Min-max scaled monetary value (0-10 scale)
                $mScore = $this->minMaxScale((float) $row->monetary_sum, $monetaries);
                
                // Overall RFM score: average of R, F, M
                $rfmScore = round(($rScore + $fScore + $mScore) / 3, 2);



                // Store the snapshot using new structure
                $insertData = [
                    'user_id' => $userId,
                    'client_id' => $client->id,
                    'snapshot_date' => $snapshotDate->toDateString(),
                ];
                
                $updateData = [
                    'txn_count' => (int) $row->txn_count,
                    'monetary_sum' => (float) $row->monetary_sum,
                    'last_txn_date' => Carbon::parse($row->last_txn_date)->toDateString(),
                    'months_since_last' => $monthsSinceLast,
                    'r_score' => $rScore,
                    'f_score' => $fScore,
                    'm_score' => $mScore,
                    'rfm_score' => $rfmScore,
                ];

                DB::table('rfm_reports')->updateOrInsert($insertData, $updateData);

                $computedCount++;
            }
        });

        return [
            'snapshot_date' => $snapshotDate->toDateString(),
            'window_start' => $windowStart->toDateString(),
            'computed' => $computedCount,
        ];
    }

    /**
     * Compute historical snapshots for trend analysis.
     * Creates snapshots for the last N months at monthly intervals.
     * Each snapshot is created for the 1st of each month.
     */
    public function computeHistoricalSnapshots(int $userId, int $monthsBack = 36): array
    {
        $results = [];
        $now = Carbon::now();

        for ($i = 0; $i <= $monthsBack; $i++) {
            // Create snapshot for the 1st of each month
            $snapshotDate = (clone $now)->subMonths($i)->startOfMonth();
            $result = $this->computeSnapshot($userId, $snapshotDate);
            $results[] = $result;
        }

        return $results;
    }

    /**
     * Min-max scaling: normalize value to 0-10 scale based on cohort
     */
    private function minMaxScale(float $value, Collection $all): float
    {
        if ($all->isEmpty()) {
            return 0.0;
        }
        
        $min = (float) $all->min();
        $max = (float) $all->max();
        
        if ($max <= $min) {
            return 0.0;
        }
        
        return round((($value - $min) / ($max - $min)) * 10, 2);
    }
}

