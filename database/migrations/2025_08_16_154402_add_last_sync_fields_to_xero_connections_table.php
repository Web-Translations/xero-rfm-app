<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('xero_connections', function (Blueprint $table) {
            $table->timestamp('last_sync_at')->nullable()->after('is_active');
            $table->integer('last_sync_invoice_count')->nullable()->after('last_sync_at');
            $table->integer('last_sync_duration_seconds')->nullable()->after('last_sync_invoice_count');
        });
    }

    public function down(): void
    {
        Schema::table('xero_connections', function (Blueprint $table) {
            $table->dropColumn(['last_sync_at', 'last_sync_invoice_count', 'last_sync_duration_seconds']);
        });
    }
};
