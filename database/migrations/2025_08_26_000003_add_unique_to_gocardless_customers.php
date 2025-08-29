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
        Schema::table('gocardless_customers', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
            $table->dropUnique(['gocardless_customer_id']);
        });
    }
};


