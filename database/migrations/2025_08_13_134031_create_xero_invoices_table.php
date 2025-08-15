<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('xero_invoices', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->char('invoice_id', 36); // Xero InvoiceID (GUID)
            $t->char('contact_id', 36);
            $t->string('status', 32);
            $t->string('type', 16); // ACCREC (sales invoices)
            $t->string('invoice_number', 50)->nullable();
            $t->date('date');
            $t->date('due_date')->nullable();
            $t->decimal('subtotal', 15, 2);
            $t->decimal('total', 15, 2);
            $t->string('currency', 10)->nullable();
            $t->dateTime('updated_date_utc');
            $t->dateTime('fully_paid_at')->nullable();
            $t->timestamps();
            $t->unique(['user_id','invoice_id']);
            $t->index(['user_id','contact_id','date']);
            $t->index(['user_id','date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('xero_invoices');
    }
};
