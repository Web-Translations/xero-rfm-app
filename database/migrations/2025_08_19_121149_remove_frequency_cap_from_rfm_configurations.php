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
        Schema::table('rfm_configurations', function (Blueprint $table) {
            $table->dropColumn('frequency_cap');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rfm_configurations', function (Blueprint $table) {
            $table->unsignedInteger('frequency_cap')->default(10);
        });
    }
};
