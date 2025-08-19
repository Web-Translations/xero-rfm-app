<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfm_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('tenant_id'); // Xero organization identifier
            
            // Recency Configuration
            $table->unsignedInteger('recency_window_months')->default(12);
            
            // Frequency Configuration  
            $table->unsignedInteger('frequency_period_months')->default(12);
            $table->unsignedInteger('frequency_cap')->default(10);
            
            // Monetary Configuration
            $table->enum('monetary_benchmark_mode', ['percentile', 'direct_value'])->default('percentile');
            $table->decimal('monetary_benchmark_percentile', 5, 2)->default(5.00); // Top X%
            $table->decimal('monetary_benchmark_value', 15, 2)->nullable(); // Direct value
            $table->boolean('monetary_use_largest_invoice')->default(true);
            
            // Overall Score Configuration
            $table->decimal('r_weight', 3, 2)->default(0.33);
            $table->decimal('f_weight', 3, 2)->default(0.33); 
            $table->decimal('m_weight', 3, 2)->default(0.34);
            
            // Metadata
            $table->string('methodology_name', 50)->default('custom_v1');
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            // Ensure one configuration per user per tenant
            $table->unique(['user_id', 'tenant_id']);
            
            // Indexes for efficient queries
            $table->index(['user_id', 'tenant_id']);
            $table->index(['is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfm_configurations');
    }
};
