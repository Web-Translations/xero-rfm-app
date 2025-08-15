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
            // Remove the problematic unique constraint entirely
            // We'll handle uniqueness in the application logic instead
            $table->dropUnique('unique_active_connection_per_user');
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
