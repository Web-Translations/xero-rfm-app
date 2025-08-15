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
            $table->boolean('is_active')->default(false)->after('expires_at');
            
            // Ensure only one active connection per user
            $table->unique(['user_id', 'is_active'], 'unique_active_connection_per_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('xero_connections', function (Blueprint $table) {
            $table->dropUnique('unique_active_connection_per_user');
            $table->dropColumn('is_active');
        });
    }
};
