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
        
        return view('rfm-config.index', [
            'config' => $config,
            'defaults' => $this->configManager->getDefaultConfiguration(),
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
            $data = $request->validate([
                'recency_window_months' => 'required|integer|min:1|max:60',
                'frequency_period_months' => 'required|integer|min:1|max:60',
                'monetary_window_months' => 'required|integer|min:1|max:60',
                'monetary_benchmark_mode' => 'required|in:percentile,direct_value',
                'monetary_benchmark_percentile' => 'required_if:monetary_benchmark_mode,percentile|numeric|min:0.1|max:50',
                'monetary_benchmark_value' => 'nullable|numeric|min:0.01',
                'monetary_use_largest_invoice' => 'boolean',
            ]);

            // Convert checkbox to boolean
            $data['monetary_use_largest_invoice'] = $request->has('monetary_use_largest_invoice');

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
}
