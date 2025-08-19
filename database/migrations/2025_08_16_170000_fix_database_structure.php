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
        // 1. Fix inconsistent invoice_id field type in excluded_invoices
        Schema::table('excluded_invoices', function (Blueprint $table) {
            $table->char('invoice_id', 36)->change();
        });

        // 2. Remove unnecessary type field from xero_invoices
        Schema::table('xero_invoices', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        // 3. Add foreign key relationship between xero_invoices.contact_id and clients.contact_id
        // First, we need to ensure the contact_id columns are properly indexed
        Schema::table('xero_invoices', function (Blueprint $table) {
            $table->index(['user_id', 'contact_id'], 'xero_invoices_user_contact_index');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->index(['user_id', 'contact_id'], 'clients_user_contact_index');
        });

        // 4. Add foreign key relationship between rfm_reports.client_id and clients.id
        // This should already exist, but let's ensure it's properly set up
        Schema::table('rfm_reports', function (Blueprint $table) {
            // Drop existing foreign key if it exists to recreate it properly
            $table->dropForeign(['client_id']);
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Restore type field in xero_invoices
        Schema::table('xero_invoices', function (Blueprint $table) {
            $table->string('type', 16)->after('status'); // ACCREC (sales invoices)
        });

        // 2. Restore invoice_id field type in excluded_invoices
        Schema::table('excluded_invoices', function (Blueprint $table) {
            $table->string('invoice_id')->change();
        });

        // 3. Drop the indexes we added
        Schema::table('xero_invoices', function (Blueprint $table) {
            $table->dropIndex('xero_invoices_user_contact_index');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex('clients_user_contact_index');
        });

        // 4. Drop the foreign key relationship for rfm_reports
        Schema::table('rfm_reports', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
        });
    }
};
