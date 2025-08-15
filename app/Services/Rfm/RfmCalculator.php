<?php

namespace App\Services\Rfm;

use App\Models\Client;
use App\Models\XeroInvoice;
use App\Models\ExcludedInvoice;
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
        $snapshotDate = $snapshotDate ?? Carbon::now();
        $windowStart = (clone $snapshotDate)->subMonths(12)->startOfDay();
        
        // Get the active connection for this user
        $activeConnection = \App\Models\XeroConnection::getActiveForUser($userId);
        if (!$activeConnection) {
            return [
                'snapshot_date' => $snapshotDate->toDateString(),
                'window_start' => $windowStart->toDateString(),
                'computed' => 0,
            ];
        }

        // Get excluded invoice IDs
        $excludedInvoiceIds = ExcludedInvoice::getExcludedInvoiceIds($userId, $activeConnection->tenant_id);

        // Aggregate sales invoice data for the rolling 12-month window
        $aggregates = XeroInvoice::query()
            ->select([
                'contact_id',
                DB::raw('COUNT(*) as txn_count'),
                DB::raw('COALESCE(SUM(total), 0) as monetary_sum'),
                DB::raw('MAX(date) as last_txn_date'),
            ])
            ->where('user_id', $userId)
            ->where('tenant_id', $activeConnection->tenant_id)
            ->where('type', 'ACCREC') // Only sales invoices for RFM analysis
            ->where('date', '>=', $windowStart->toDateString())
            ->where('date', '<=', $snapshotDate->toDateString())
            ->when(!empty($excludedInvoiceIds), function ($query) use ($excludedInvoiceIds) {
                $query->whereNotIn('invoice_id', $excludedInvoiceIds);
            })
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
            ->where('tenant_id', $activeConnection->tenant_id)
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
            &$computedCount,
            $activeConnection
        ) {
            // Get all clients that should have RFM scores for this snapshot
            $allClients = Client::query()
                ->where('user_id', $userId)
                ->where('tenant_id', $activeConnection->tenant_id)
                ->get();

            foreach ($allClients as $client) {
                $contactId = $client->contact_id;
                $row = $aggregates->get($contactId);

                if ($row) {
                    // Client has invoices in the window - calculate RFM scores
                    $monthsSinceLast = max(0, Carbon::parse($row->last_txn_date)->diffInMonths($snapshotDate));
                    
                    // R: 10 - months since last transaction (minimum 0)
                    $rScore = max(0, 10 - $monthsSinceLast);
                    
                    // F: Number of invoices in past 12 months (capped at 10)
                    $fScore = min(10, (int) $row->txn_count);
                    
                    // M: Min-max scaled monetary value (0-10 scale)
                    $mScore = $this->minMaxScale((float) $row->monetary_sum, $monetaries);
                    
                    // Overall RFM score: average of R, F, M
                    $rfmScore = round(($rScore + $fScore + $mScore) / 3, 2);

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
                } else {
                    // Client has no invoices in the window - set all scores to 0
                    $insertData = [
                        'user_id' => $userId,
                        'client_id' => $client->id,
                        'snapshot_date' => $snapshotDate->toDateString(),
                    ];
                    
                    $updateData = [
                        'txn_count' => 0,
                        'monetary_sum' => 0,
                        'last_txn_date' => null,
                        'months_since_last' => null,
                        'r_score' => 0,
                        'f_score' => 0,
                        'm_score' => 0,
                        'rfm_score' => 0,
                    ];
                }

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

