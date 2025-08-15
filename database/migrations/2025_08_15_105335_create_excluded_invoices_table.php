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
        Schema::create('excluded_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('tenant_id'); // To support multi-organization
            $table->string('invoice_id'); // Xero invoice ID
            $table->timestamps();
            
            // Ensure each user can only exclude an invoice once per organization
            $table->unique(['user_id', 'tenant_id', 'invoice_id'], 'unique_excluded_invoice');
            
            // Index for efficient lookups
            $table->index(['user_id', 'tenant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('excluded_invoices');
    }
};
