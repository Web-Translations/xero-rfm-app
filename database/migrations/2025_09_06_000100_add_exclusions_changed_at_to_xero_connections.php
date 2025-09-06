<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('xero_connections', function (Blueprint $table) {
            if (!Schema::hasColumn('xero_connections', 'exclusions_changed_at')) {
                $table->timestamp('exclusions_changed_at')->nullable()->after('last_sync_invoice_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('xero_connections', function (Blueprint $table) {
            if (Schema::hasColumn('xero_connections', 'exclusions_changed_at')) {
                $table->dropColumn('exclusions_changed_at');
            }
        });
    }
};


