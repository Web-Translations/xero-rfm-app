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
        Schema::table('xero_invoices', function (Blueprint $table) {
            $table->string('tenant_id')->after('user_id');
            $table->index(['user_id', 'tenant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('xero_invoices', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'tenant_id']);
            $table->dropColumn('tenant_id');
        });
    }
};
