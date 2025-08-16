<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('xero_connections', function (Blueprint $table) {
            $table->dropColumn(['last_sync_duration_seconds', 'last_sync_started_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('xero_connections', function (Blueprint $table) {
            $table->integer('last_sync_duration_seconds')->nullable()->after('last_sync_invoice_count');
            $table->timestamp('last_sync_started_at')->nullable()->after('last_sync_duration_seconds');
        });
    }
};
