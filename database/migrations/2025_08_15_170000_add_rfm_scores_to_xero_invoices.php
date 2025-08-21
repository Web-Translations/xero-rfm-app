<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('xero_invoices', function (Blueprint $table) {
            $table->decimal('r_score', 4, 2)->nullable()->after('fully_paid_at');
            $table->decimal('f_score', 4, 2)->nullable()->after('r_score');
            $table->decimal('m_score', 4, 2)->nullable()->after('f_score');
            $table->decimal('rfm_score', 4, 2)->nullable()->after('m_score');
            $table->timestamp('rfm_calculated_at')->nullable()->after('rfm_score');
            
            // Index for efficient RFM queries
            $table->index(['user_id', 'tenant_id', 'date', 'rfm_score']);
        });
    }

    public function down(): void
    {
        Schema::table('xero_invoices', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'tenant_id', 'date', 'rfm_score']);
            $table->dropColumn(['r_score', 'f_score', 'm_score', 'rfm_score', 'rfm_calculated_at']);
        });
    }
};

