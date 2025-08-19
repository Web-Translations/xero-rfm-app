<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Added missing import for DB facade

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create a new slimmed down table structure
        Schema::create('rfm_reports_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->date('snapshot_date'); // When this RFM calculation was made
            
            // Only store the final scores
            $table->unsignedTinyInteger('r_score'); // Recency score (0-10)
            $table->unsignedTinyInteger('f_score'); // Frequency score (0-10)
            $table->decimal('m_score', 4, 2); // Monetary score (0-10)
            $table->decimal('rfm_score', 4, 2); // Overall RFM score (0-10)
            
            // Reference to the configuration used for this calculation
            $table->foreignId('rfm_configuration_id')->nullable()->constrained()->nullOnDelete();
            
            $table->timestamps();
            
            // Indexes for efficient queries
            $table->unique(['user_id', 'client_id', 'snapshot_date']); // Prevent duplicate snapshots
            $table->index(['user_id', 'client_id', 'snapshot_date']); // For client trend queries
            $table->index(['snapshot_date']); // For date range queries
            $table->index(['user_id', 'snapshot_date']); // For user-specific date queries
            $table->index(['rfm_configuration_id']); // For configuration-based queries
        });

        // Copy existing data to new table (if any exists)
        if (Schema::hasTable('rfm_reports')) {
            DB::statement('
                INSERT INTO rfm_reports_new (user_id, client_id, snapshot_date, r_score, f_score, m_score, rfm_score, created_at, updated_at)
                SELECT user_id, client_id, snapshot_date, r_score, f_score, m_score, rfm_score, created_at, updated_at
                FROM rfm_reports
            ');
        }

        // Drop the old table and rename the new one
        Schema::dropIfExists('rfm_reports');
        Schema::rename('rfm_reports_new', 'rfm_reports');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the original table structure if needed
        Schema::create('rfm_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->date('snapshot_date');
            $table->unsignedInteger('txn_count');
            $table->decimal('monetary_sum', 15, 2);
            $table->date('last_txn_date')->nullable();
            $table->unsignedInteger('months_since_last')->nullable();
            $table->unsignedTinyInteger('r_score');
            $table->unsignedTinyInteger('f_score');
            $table->unsignedTinyInteger('m_score');
            $table->decimal('rfm_score', 4, 2);
            $table->timestamps();
            
            $table->unique(['user_id', 'client_id', 'snapshot_date']);
            $table->index(['user_id', 'client_id', 'snapshot_date']);
            $table->index(['snapshot_date']);
            $table->index(['user_id', 'snapshot_date']);
        });
    }
};
