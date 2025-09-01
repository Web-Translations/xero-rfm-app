<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gocardless_customers', function (Blueprint $table) {
            $table->unique('user_id');
            $table->unique('gocardless_customer_id');
        });
    }

    public function down(): void
    {
        // Guard for rollback order (if the table was dropped first, skip dropping indexes)
        if (! Schema::hasTable('gocardless_customers')) {
            return;
        }

        Schema::table('gocardless_customers', function (Blueprint $table) {
            // Use explicit index names to avoid ambiguity across drivers
            try {
                $table->dropUnique('gocardless_customers_user_id_unique');
            } catch (\Throwable $e) {
                // index may not exist (e.g. if it was never created or already removed)
            }
            try {
                $table->dropUnique('gocardless_customers_gocardless_customer_id_unique');
            } catch (\Throwable $e) {
                // ignore if absent
            }
        });
    }
};


