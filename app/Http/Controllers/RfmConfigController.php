<?php

namespace App\Http\Controllers;

use App\Services\Rfm\RfmConfigurationManager;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RfmConfigController extends Controller
{
    public function __construct(
        private RfmConfigurationManager $configManager
    ) {}

    /**
     * Display the RFM configuration page
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $activeConnection = $user->getActiveXeroConnection();
        
        if (!$activeConnection) {
            return redirect()->route('dashboard')->withErrors('Please connect a Xero organisation first.');
        }

        $config = $this->configManager->getConfiguration($user->id, $activeConnection->tenant_id);
        $hasInvoices = \App\Models\XeroInvoice::where('user_id', $user->id)
            ->where('tenant_id', $activeConnection->tenant_id)
            ->exists();
        
        return view('rfm-config.index', [
            'config' => $config,
            'defaults' => $this->configManager->getDefaultConfiguration(),
            'hasInvoices' => $hasInvoices,
        ]);
    }

    /**
     * Save RFM configuration
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $activeConnection = $user->getActiveXeroConnection();
        
        if (!$activeConnection) {
            return redirect()->route('dashboard')->withErrors('Please connect a Xero organisation first.');
        }

        try {
            // Normalize checkbox booleans before validating
            $payload = $request->all();
            $payload['monetary_benchmark_mode'] = $request->input('monetary_benchmark_mode');
            $payload['monetary_use_largest_invoice'] = $request->has('monetary_use_largest_invoice');
            $payload['auto_adjust_window'] = $request->has('auto_adjust_window');

            $data = \Validator::make($payload, [
                'recency_window_months' => 'required|integer|min:1|max:60',
                'frequency_period_months' => 'required|integer|min:1|max:60',
                'monetary_window_months' => 'required|integer|min:1|max:60',
                'monetary_benchmark_mode' => 'required|in:percentile,direct_value',
                'monetary_benchmark_percentile' => 'required_if:monetary_benchmark_mode,percentile|numeric|min:0.1|max:50',
                'monetary_benchmark_value' => 'nullable|numeric|min:0.01',
                'monetary_use_largest_invoice' => 'boolean',
                'auto_adjust_window' => 'boolean',
                'frequency_autoadjust_threshold' => 'sometimes|integer|min:1|max:10',
            ])->validate();

            // Booleans have been normalized above

            // Handle monetary benchmark value based on mode
            if ($data['monetary_benchmark_mode'] === 'percentile') {
                $data['monetary_benchmark_value'] = null;
            } else {
                // For direct_value mode, ensure we have a valid benchmark value
                if (empty($data['monetary_benchmark_value'])) {
                    throw ValidationException::withMessages([
                        'monetary_benchmark_value' => 'A benchmark value is required when using direct value mode.'
                    ]);
                }
            }

            $this->configManager->updateConfiguration($user->id, $activeConnection->tenant_id, $data);

            return redirect()->route('rfm.config.index')->with('status', 'RFM configuration saved successfully!');

        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }
    }

    /**
     * Save configuration and run a full recalculation (current + historical), same as RFM page.
     */
    public function saveAndRecalculate(Request $request, \App\Services\Rfm\RfmCalculator $calculator)
    {
        $user = $request->user();
        $activeConnection = $user->getActiveXeroConnection();
        if (!$activeConnection) {
            return redirect()->route('dashboard')->withErrors('Please connect a Xero organisation first.');
        }

        // Block if no invoices exist
        $hasInvoices = \App\Models\XeroInvoice::where('user_id', $user->id)
            ->where('tenant_id', $activeConnection->tenant_id)
            ->exists();
        if (!$hasInvoices) {
            return redirect()->route('rfm.config.index')->withErrors('No invoices found. Please sync invoices before recalculating.');
        }

        // Reuse store() validation to persist config
        $data = $request->validate([
            'recency_window_months' => 'required|integer|min:1|max:60',
            'frequency_period_months' => 'required|integer|min:1|max:60',
            'monetary_window_months' => 'required|integer|min:1|max:60',
            'monetary_benchmark_mode' => 'required|in:percentile,direct_value',
            'monetary_benchmark_percentile' => 'required_if:monetary_benchmark_mode,percentile|numeric|min:0.1|max:50',
            'monetary_benchmark_value' => 'nullable|numeric|min:0.01',
            'monetary_use_largest_invoice' => 'sometimes|boolean',
            'auto_adjust_window' => 'sometimes|boolean',
            'frequency_autoadjust_threshold' => 'sometimes|integer|min:1|max:10',
        ]);

        // Normalize checkbox (even though not shown in UI now)
        $data['monetary_use_largest_invoice'] = $request->has('monetary_use_largest_invoice');
        if ($data['monetary_benchmark_mode'] === 'percentile') {
            $data['monetary_benchmark_value'] = null;
        } elseif (empty($data['monetary_benchmark_value'])) {
            return redirect()->back()->withErrors(['monetary_benchmark_value' => 'A benchmark value is required in direct value mode.'])->withInput();
        }
        $data['auto_adjust_window'] = $request->has('auto_adjust_window');
        if (!isset($data['frequency_autoadjust_threshold'])) {
            $data['frequency_autoadjust_threshold'] = 5;
        }

        $config = $this->configManager->updateConfiguration($user->id, $activeConnection->tenant_id, $data);

        // Auto-adjust flow mirrors RFM Scores page
        $finalWindow = null; $observations = []; $fallback = false;
        if ($config->auto_adjust_window) {
            $chooser = app(\App\Services\Rfm\RfmWindowChooser::class);
            $choice = $chooser->chooseFinalWindow($user->id, $activeConnection->tenant_id, (int) ($config->frequency_autoadjust_threshold ?? 5));
            $finalWindow = $choice['final_window'] ?? null;
            $observations = $choice['observations'] ?? [];
            $fallback = (bool) ($choice['fallback'] ?? false);
        }

        if ($finalWindow) {
            // Persist windows so the config and downstream UI reflect actual settings
            \App\Models\RfmConfiguration::where('user_id', $user->id)
                ->where('tenant_id', $activeConnection->tenant_id)
                ->where('is_active', true)
                ->update([
                    'recency_window_months' => $finalWindow,
                    'frequency_period_months' => $finalWindow,
                    'monetary_window_months' => $finalWindow,
                    'updated_at' => now(),
                ]);
            $config = \App\Models\RfmConfiguration::getOrCreateDefault($user->id, $activeConnection->tenant_id);
        }

        // Perform the same computation as the RFM Scores sync
        $currentResult = $calculator->computeSnapshot($user->id, null, $config);
        $historicalResults = $calculator->computeHistoricalSnapshots($user->id, 36, $config);
        $totalHistorical = array_sum(array_column($historicalResults, 'computed'));
        $cleanedUp = $calculator->cleanupOldSnapshots($user->id);

        $status = "Saved configuration. Synced RFM data: {$currentResult['computed']} current scores and {$totalHistorical} historical snapshots created. Cleaned up {$cleanedUp} old snapshots.";
        if ($finalWindow) {
            $status .= " Using window {$finalWindow} months.";
            session()->flash('rfm_auto_window', (string) $finalWindow);
            if (!empty($observations)) session()->flash('rfm_auto_obs', $observations);
            if ($fallback) session()->flash('rfm_auto_fallback', true);
        }

        return redirect()->route('rfm.index')->with('status', $status);
    }

    /**
     * Reset configuration to defaults
     */
    public function reset(Request $request)
    {
        $user = $request->user();
        $activeConnection = $user->getActiveXeroConnection();
        
        if (!$activeConnection) {
            return redirect()->route('dashboard')->withErrors('Please connect a Xero organisation first.');
        }

        $this->configManager->resetToDefaults($user->id, $activeConnection->tenant_id);

        return redirect()->route('rfm.config.index')->with('status', 'RFM configuration reset to defaults!');
    }

    /**
     * Recalculate RFM scores with new configuration
     */
    public function recalculate(Request $request)
    {
        $user = $request->user();
        $activeConnection = $user->getActiveXeroConnection();
        
        if (!$activeConnection) {
            return redirect()->route('dashboard')->withErrors('Please connect a Xero organisation first.');
        }

        // This will be implemented when we update the RfmCalculator
        return redirect()->route('rfm.index')->with('status', 'RFM recalculation triggered! Use the sync button on the RFM page to recalculate with new settings.');
    }

    /**
     * Preview monetary benchmark for percentile mode (AJAX)
     */
    public function benchmarkPreview(Request $request)
    {
        $user = $request->user();
        $activeConnection = $user->getActiveXeroConnection();
        if (!$activeConnection) {
            return response()->json(['error' => 'No active organisation.'], 400);
        }

        $validated = $request->validate([
            'monetary_window_months' => 'required|integer|min:1|max:60',
            'percentile' => 'required|numeric|min:0.1|max:50',
        ]);

        // Compute window
        $endDate = now();
        $startDate = now()->copy()->subMonths((int) $validated['monetary_window_months']);

        // Get excluded invoice IDs
        $excludedIds = \App\Models\ExcludedInvoice::getExcludedInvoiceIds($user->id, $activeConnection->tenant_id);

        // Pull invoices in monetary window
        $invoices = \App\Models\XeroInvoice::query()
            ->select(['contact_id','date','total'])
            ->where('user_id', $user->id)
            ->where('tenant_id', $activeConnection->tenant_id)
            ->where('date', '>=', $startDate->toDateString())
            ->where('date', '<=', $endDate->toDateString())
            ->when(!empty($excludedIds), function ($q) use ($excludedIds) {
                $q->whereNotIn('invoice_id', $excludedIds);
            })
            ->get();

        if ($invoices->isEmpty()) {
            return response()->json([
                'benchmark' => null,
                'sampleSize' => 0,
                'windowStart' => $startDate->toDateString(),
                'windowEnd' => $endDate->toDateString(),
                'message' => 'No invoices found in this window.',
            ]);
        }

        // Largest invoice per customer in-window
        $largestPerCustomer = $invoices->groupBy('contact_id')
            ->map(function ($rows) { return $rows->max('total'); })
            ->values()
            ->sort()
            ->values();

        $percentile = (float) $validated['percentile'];
        $q = max(0, min(1, 1 - ($percentile / 100)));

        $benchmark = $this->quantile($largestPerCustomer, $q);
        if (!$benchmark || $benchmark <= 0) {
            $median = $largestPerCustomer->median();
            $benchmark = $median ?: null;
        }

        return response()->json([
            'benchmark' => $benchmark ? round((float) $benchmark, 2) : null,
            'sampleSize' => $largestPerCustomer->count(),
            'windowStart' => $startDate->toDateString(),
            'windowEnd' => $endDate->toDateString(),
        ]);
    }

    private function quantile(\Illuminate\Support\Collection $sorted, float $q): ?float
    {
        if ($sorted->isEmpty()) { return null; }
        if ($q <= 0) { return (float) $sorted->first(); }
        if ($q >= 1) { return (float) $sorted->last(); }
        $pos = ($sorted->count() - 1) * $q;
        $base = (int) floor($pos);
        $rest = $pos - $base;
        if ($sorted->has($base + 1)) {
            return (float) $sorted->get($base) + $rest * ((float) $sorted->get($base + 1) - (float) $sorted->get($base));
        }
        return (float) $sorted->get($base);
    }
}
