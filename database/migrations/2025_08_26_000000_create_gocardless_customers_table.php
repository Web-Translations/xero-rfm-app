<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('gocardless_customers')) {
            Schema::create('gocardless_customers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('gocardless_customer_id');
                $table->string('email');
                $table->string('given_name')->nullable();
                $table->string('family_name')->nullable();
                $table->string('company_name')->nullable();
                $table->string('address_line1')->nullable();
                $table->string('address_line2')->nullable();
                $table->string('city')->nullable();
                $table->string('region')->nullable();
                $table->string('postal_code')->nullable();
                $table->string('country_code', 2)->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('gocardless_customers');
    }
};


