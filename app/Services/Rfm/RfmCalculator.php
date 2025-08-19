<?php

namespace App\Services\Rfm;

use App\Models\Client;
use App\Models\XeroInvoice;
use App\Models\ExcludedInvoice;
use App\Models\RfmConfiguration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RfmCalculator
{
    /**
     * Compute RFM scores for all clients using configurable parameters.
     * Stores snapshot with timestamp for historical analysis.
     */
    public function computeSnapshot(int $userId, Carbon $snapshotDate = null, ?RfmConfiguration $config = null): array
    {
        $snapshotDate = $snapshotDate ?? Carbon::now();
        
        // Get the active connection for this user
        $activeConnection = \App\Models\XeroConnection::getActiveForUser($userId);
        if (!$activeConnection) {
            return [
                'snapshot_date' => $snapshotDate->toDateString(),
                'window_start' => null,
                'computed' => 0,
            ];
        }

        // Get or create configuration
        if (!$config) {
            $config = RfmConfiguration::getOrCreateDefault($userId, $activeConnection->tenant_id);
        }

        // Calculate windows for each component
        $recencyWindowStart = (clone $snapshotDate)->subMonths($config->recency_window_months)->startOfDay();
        $frequencyWindowStart = (clone $snapshotDate)->subMonths($config->frequency_period_months)->startOfDay();
        $monetaryWindowStart = (clone $snapshotDate)->subMonths($config->monetary_window_months)->startOfDay();



        // Get excluded invoice IDs
        $excludedInvoiceIds = ExcludedInvoice::getExcludedInvoiceIds($userId, $activeConnection->tenant_id);

        // Get ALL invoices for the user (we'll filter by window later for each component)
        $allInvoices = XeroInvoice::query()
            ->select([
                'contact_id',
                'date',
                'total',
            ])
            ->where('user_id', $userId)
            ->where('tenant_id', $activeConnection->tenant_id)
            ->when(!empty($excludedInvoiceIds), function ($query) use ($excludedInvoiceIds) {
                $query->whereNotIn('invoice_id', $excludedInvoiceIds);
            })
            ->get();

        if ($allInvoices->isEmpty()) {
            return [
                'snapshot_date' => $snapshotDate->toDateString(),
                'window_start' => min($recencyWindowStart, $frequencyWindowStart, $monetaryWindowStart)->toDateString(),
                'computed' => 0,
            ];
        }

        // Group invoices by contact_id
        $invoicesByContact = $allInvoices->groupBy('contact_id');

        // Calculate monetary benchmark using invoices in the monetary window
        $monetaryWindowInvoices = $allInvoices->filter(function ($invoice) use ($monetaryWindowStart, $snapshotDate) {
            return $invoice->date >= $monetaryWindowStart->toDateString() && $invoice->date <= $snapshotDate->toDateString();
        });
        
        $largestInvoices = $monetaryWindowInvoices->groupBy('contact_id')
            ->map(function ($contactInvoices) {
                return $contactInvoices->max('total');
            })
            ->values();

        $monetaryBenchmark = $this->calculateMonetaryBenchmark($config, $largestInvoices);

        // Get clients that have ANY transactions (we'll filter by window later)
        $clients = Client::query()
            ->where('user_id', $userId)
            ->where('tenant_id', $activeConnection->tenant_id)
            ->whereIn('contact_id', $invoicesByContact->keys()->all())
            ->get()
            ->keyBy('contact_id');

        // Compute and store RFM scores
        $computedCount = 0;

        DB::transaction(function () use (
            $clients,
            $invoicesByContact,
            $snapshotDate,
            $userId,
            &$computedCount,
            $activeConnection,
            $config,
            $monetaryBenchmark,
            $recencyWindowStart,
            $frequencyWindowStart,
            $monetaryWindowStart
        ) {
            foreach ($clients as $client) {
                // Get all invoices for this client
                $contactId = $client->contact_id;
                $contactInvoices = $invoicesByContact->get($contactId, collect());

                if ($contactInvoices->isEmpty()) {
                    continue;
                }

                // Calculate R: Recency (filter by recency window)
                $recencyInvoices = $contactInvoices->filter(function ($invoice) use ($recencyWindowStart, $snapshotDate) {
                    return $invoice->date >= $recencyWindowStart->toDateString() && $invoice->date <= $snapshotDate->toDateString();
                });
                
                if ($recencyInvoices->isEmpty()) {
                    $rScore = 0.0;
                } else {
                    $lastInvoiceDate = $recencyInvoices->max('date');
                    $monthsSinceLast = max(0, Carbon::parse($lastInvoiceDate)->diffInMonths($snapshotDate));
                    $rScore = $this->calculateRecencyScore($monthsSinceLast, $config);
                }

                // Calculate F: Frequency (filter by frequency window)
                $frequencyInvoices = $contactInvoices->filter(function ($invoice) use ($frequencyWindowStart, $snapshotDate) {
                    return $invoice->date >= $frequencyWindowStart->toDateString() && $invoice->date <= $snapshotDate->toDateString();
                });
                $fScore = $this->calculateFrequencyScore($frequencyInvoices->count(), $config);

                // Calculate M: Monetary (filter by monetary window)
                $monetaryInvoices = $contactInvoices->filter(function ($invoice) use ($monetaryWindowStart, $snapshotDate) {
                    return $invoice->date >= $monetaryWindowStart->toDateString() && $invoice->date <= $snapshotDate->toDateString();
                });
                $largestInvoice = $monetaryInvoices->max('total') ?? 0;
                $mScore = $this->calculateMonetaryScore($largestInvoice, $monetaryBenchmark);


                // Overall RFM score: simple average of R, F, M
                $rfmScore = round(($rScore + $fScore + $mScore) / 3, 2);

                $insertData = [
                    'user_id' => $userId,
                    'client_id' => $client->id,
                    'snapshot_date' => $snapshotDate->toDateString(),
                    'rfm_configuration_id' => $config->id,
                ];
                
                $updateData = [
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
            'window_start' => min($recencyWindowStart, $frequencyWindowStart, $monetaryWindowStart)->toDateString(),
            'computed' => $computedCount,
        ];
    }

    /**
     * Calculate recency score based on configuration
     */
    private function calculateRecencyScore(int $monthsSinceLast, RfmConfiguration $config): float
    {
        $windowMonths = $config->recency_window_months;
        $score = 10 - (10 / max($windowMonths, 0.0001)) * $monthsSinceLast;
        return round(max(0, min(10, $score)), 2);
    }

    /**
     * Calculate frequency score based on configuration
     */
    private function calculateFrequencyScore(int $transactionCount, RfmConfiguration $config): int
    {
        return min($transactionCount, 10); // Cap at 10
    }

    /**
     * Calculate monetary score based on configuration
     */
    private function calculateMonetaryScore(float $largestInvoice, ?float $benchmark): float
    {
        if (!$benchmark || $benchmark <= 0) {
            // If no benchmark, use a fallback calculation based on the invoice value
            // This prevents all customers from getting 0 monetary scores
            if ($largestInvoice <= 0) {
                return 0.0;
            }
            
            // Use a simple scale: £1000 = 5 points, £2000 = 10 points
            $fallbackScore = min(10, ($largestInvoice / 2000) * 10);
            return round($fallbackScore, 2);
        }
        
        $score = ($largestInvoice / $benchmark) * 10;
        return round(max(0, min(10, $score)), 2);
    }

    /**
     * Calculate monetary benchmark based on configuration
     */
    private function calculateMonetaryBenchmark(RfmConfiguration $config, Collection $largestInvoices): ?float
    {
        if ($config->monetary_benchmark_mode === 'direct_value') {
            return $config->monetary_benchmark_value;
        }

        // Percentile mode
        if ($largestInvoices->isEmpty()) {
            return null;
        }

        $sorted = $largestInvoices->sort()->values();
        $percentile = $config->monetary_benchmark_percentile;
        
        // Calculate the (100 - percentile)th percentile
        $q = max(0, min(1, 1 - ($percentile / 100)));
        $benchmark = $this->quantile($sorted, $q);
        
        // Ensure we have a reasonable benchmark value
        if (!$benchmark || $benchmark <= 0) {
            // Fallback to median if percentile calculation fails
            $median = $sorted->median();
            return $median > 0 ? $median : null;
        }
        
        return $benchmark;
    }

    /**
     * Quantile helper: q in [0,1], linear interpolation
     */
    private function quantile(Collection $sorted, float $q): ?float
    {
        if ($sorted->isEmpty()) {
            return null;
        }
        
        if ($q <= 0) {
            return $sorted->first();
        }
        
        if ($q >= 1) {
            return $sorted->last();
        }
        
        $pos = ($sorted->count() - 1) * $q;
        $base = (int) floor($pos);
        $rest = $pos - $base;
        
        if ($sorted->has($base + 1)) {
            return $sorted->get($base) + $rest * ($sorted->get($base + 1) - $sorted->get($base));
        } else {
            return $sorted->get($base);
        }
    }

    /**
     * Compute historical snapshots for trend analysis.
     * Creates snapshots for the last N months at monthly intervals.
     * Each snapshot is created for the 1st of each month.
     */
    public function computeHistoricalSnapshots(int $userId, int $monthsBack = 36, ?RfmConfiguration $config = null): array
    {
        $results = [];
        $now = Carbon::now();

        for ($i = 0; $i <= $monthsBack; $i++) {
            $snapshotDate = (clone $now)->subMonths($i)->startOfMonth();
            
            $result = $this->computeSnapshot($userId, $snapshotDate, $config);
            $results[] = $result;
        }

        return $results;
    }

    /**
     * Min-max scaling: normalize value to 0-10 scale based on cohort
     * This is kept for backward compatibility but not used in the new configurable system
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

