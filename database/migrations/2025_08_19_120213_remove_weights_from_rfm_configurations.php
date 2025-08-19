<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rfm_configurations', function (Blueprint $table) {
            $table->dropColumn(['r_weight', 'f_weight', 'm_weight']);
        });
    }

    public function down(): void
    {
        Schema::table('rfm_configurations', function (Blueprint $table) {
            $table->decimal('r_weight', 3, 2)->default(0.33);
            $table->decimal('f_weight', 3, 2)->default(0.33);
            $table->decimal('m_weight', 3, 2)->default(0.34);
        });
    }
};
