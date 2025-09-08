<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rfm_configurations', function (Blueprint $table) {
            if (!Schema::hasColumn('rfm_configurations', 'auto_adjust_window')) {
                $table->boolean('auto_adjust_window')->default(true)->after('monetary_use_largest_invoice');
            }
            if (!Schema::hasColumn('rfm_configurations', 'frequency_autoadjust_threshold')) {
                $table->unsignedTinyInteger('frequency_autoadjust_threshold')->default(5)->after('auto_adjust_window');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rfm_configurations', function (Blueprint $table) {
            if (Schema::hasColumn('rfm_configurations', 'frequency_autoadjust_threshold')) {
                $table->dropColumn('frequency_autoadjust_threshold');
            }
            if (Schema::hasColumn('rfm_configurations', 'auto_adjust_window')) {
                $table->dropColumn('auto_adjust_window');
            }
        });
    }
};


