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
            // Ensure supporting index exists for foreign keys before dropping composite unique
            $table->index('user_id');
        });

        Schema::table('xero_connections', function (Blueprint $table) {
            // Remove the problematic unique constraint safely
            if (Schema::hasColumn('xero_connections', 'is_active')) {
                try {
                    $table->dropUnique('unique_active_connection_per_user');
                } catch (\Throwable $e) {
                    // Some MariaDB versions require explicit index name resolution; ignore if already dropped
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('xero_connections', function (Blueprint $table) {
            // Restore the original constraint
            $table->unique(['user_id', 'is_active'], 'unique_active_connection_per_user');
        });
    }
};
