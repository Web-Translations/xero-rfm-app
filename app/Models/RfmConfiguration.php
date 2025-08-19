<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RfmConfiguration extends Model
{
    protected $fillable = [
        'user_id',
        'tenant_id',
        'recency_window_months',
        'frequency_period_months',
        'monetary_window_months',
        'monetary_benchmark_mode',
        'monetary_benchmark_percentile',
        'monetary_benchmark_value',
        'monetary_use_largest_invoice',
        'methodology_name',
        'is_active',
    ];

    protected $casts = [
        'monetary_benchmark_percentile' => 'decimal:2',
        'monetary_benchmark_value' => 'decimal:2',
        'monetary_use_largest_invoice' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rfmReports(): HasMany
    {
        return $this->hasMany(RfmReport::class);
    }

    /**
     * Get configuration for a specific user and tenant
     */
    public static function getForUser(int $userId, string $tenantId): ?self
    {
        return self::where('user_id', $userId)
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get or create default configuration for a user
     */
    public static function getOrCreateDefault(int $userId, string $tenantId): self
    {
        $config = self::getForUser($userId, $tenantId);
        
        if (!$config) {
            $config = self::create([
                'user_id' => $userId,
                'tenant_id' => $tenantId,
                // Defaults are set in the migration
            ]);
        }
        
        return $config;
    }

    /**
     * Get the active benchmark value for monetary calculations
     */
    public function getActiveBenchmark(): ?float
    {
        if ($this->monetary_benchmark_mode === 'direct_value') {
            return $this->monetary_benchmark_value;
        }
        
        // For percentile mode, we'll need to calculate this dynamically
        // This will be handled in the RfmCalculator service
        return null;
    }
}
