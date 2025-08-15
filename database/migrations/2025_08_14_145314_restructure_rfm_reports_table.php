<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the existing table and recreate with new structure
        Schema::dropIfExists('rfm_reports');
        
        Schema::create('rfm_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->date('snapshot_date'); // When this RFM calculation was made
            $table->unsignedInteger('txn_count'); // Number of invoices in last 12 months
            $table->decimal('monetary_sum', 15, 2); // Total revenue in last 12 months
            $table->date('last_txn_date')->nullable(); // Last transaction date
            $table->unsignedInteger('months_since_last')->nullable(); // Months from last transaction to snapshot
            $table->unsignedTinyInteger('r_score'); // Recency score (0-10)
            $table->unsignedTinyInteger('f_score'); // Frequency score (0-10)
            $table->unsignedTinyInteger('m_score'); // Monetary score (0-10)
            $table->decimal('rfm_score', 4, 2); // Overall RFM score (0-10)
            $table->timestamps();
            
            // Indexes for efficient queries
            $table->unique(['user_id', 'client_id', 'snapshot_date']); // Prevent duplicate snapshots
            $table->index(['user_id', 'client_id', 'snapshot_date']); // For client trend queries
            $table->index(['snapshot_date']); // For date range queries
            $table->index(['user_id', 'snapshot_date']); // For user-specific date queries
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfm_reports');
    }
};
