<?php

namespace App\Services\Rfm;

use App\Models\RfmConfiguration;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RfmConfigurationManager
{
    /**
     * Get or create configuration for a user
     */
    public function getConfiguration(int $userId, string $tenantId): RfmConfiguration
    {
        return RfmConfiguration::getOrCreateDefault($userId, $tenantId);
    }

    /**
     * Update configuration for a user
     */
    public function updateConfiguration(int $userId, string $tenantId, array $data): RfmConfiguration
    {
        $this->validateConfiguration($data);
        
        $config = RfmConfiguration::getOrCreateDefault($userId, $tenantId);
        $config->update($data);
        
        return $config->fresh();
    }

    /**
     * Reset configuration to defaults
     */
    public function resetToDefaults(int $userId, string $tenantId): RfmConfiguration
    {
        $config = RfmConfiguration::getOrCreateDefault($userId, $tenantId);
        
        $config->update([
            'recency_window_months' => 12,
            'frequency_period_months' => 12,
            'monetary_window_months' => 12,
            'monetary_benchmark_mode' => 'percentile',
            'monetary_benchmark_percentile' => 5.00,
            'monetary_benchmark_value' => null,
            'monetary_use_largest_invoice' => true,
            'methodology_name' => 'default_v1',
        ]);
        
        return $config->fresh();
    }

    /**
     * Validate configuration data
     */
    private function validateConfiguration(array $data): void
    {
        $validator = Validator::make($data, [
            'recency_window_months' => 'required|integer|min:1|max:60',
            'frequency_period_months' => 'required|integer|min:1|max:60',
            'monetary_window_months' => 'required|integer|min:1|max:60',
            'monetary_benchmark_mode' => 'required|in:percentile,direct_value',
            'monetary_benchmark_percentile' => 'required_if:monetary_benchmark_mode,percentile|numeric|min:0.1|max:50',
            'monetary_benchmark_value' => 'nullable|numeric|min:0.01',
            'monetary_use_largest_invoice' => 'boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Additional validation for monetary benchmark value
        if (isset($data['monetary_benchmark_mode']) && $data['monetary_benchmark_mode'] === 'direct_value') {
            if (empty($data['monetary_benchmark_value'])) {
                throw ValidationException::withMessages([
                    'monetary_benchmark_value' => 'A benchmark value is required when using direct value mode.'
                ]);
            }
        }
    }

    /**
     * Get default configuration values
     */
    public function getDefaultConfiguration(): array
    {
        return [
            'recency_window_months' => 12,
            'frequency_period_months' => 12,
            'monetary_window_months' => 12,
            'monetary_benchmark_mode' => 'percentile',
            'monetary_benchmark_percentile' => 5.00,
            'monetary_benchmark_value' => null,
            'monetary_use_largest_invoice' => true,
        ];
    }
}
