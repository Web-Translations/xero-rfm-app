<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rfm_configurations', function (Blueprint $table) {
            $table->unsignedInteger('monetary_window_months')->default(12)->after('frequency_period_months');
        });
    }

    public function down(): void
    {
        Schema::table('rfm_configurations', function (Blueprint $table) {
            $table->dropColumn('monetary_window_months');
        });
    }
};
