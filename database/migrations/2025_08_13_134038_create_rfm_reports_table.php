<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfm_reports', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('client_id')->constrained()->cascadeOnDelete(); // clients.id
            $t->enum('period_granularity', ['quarter','month'])->default('quarter');
            $t->date('period_start');
            $t->date('period_end');
            $t->unsignedInteger('window_months')->default(12);
            $t->unsignedInteger('txn_count');
            $t->decimal('monetary_sum', 15, 2);
            $t->date('last_txn_date')->nullable();
            $t->unsignedInteger('months_since_last');
            $t->unsignedTinyInteger('r_score');
            $t->unsignedTinyInteger('f_score');
            $t->unsignedTinyInteger('m_score');
            $t->decimal('rfm_score', 4, 2);
            $t->string('methodology', 50)->default('B2B_baseline_v1');
            $t->timestamps();
            $t->unique(['client_id','period_granularity','period_start','period_end'], 'uq_client_id_period');
            $t->index(['user_id','period_granularity','period_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfm_reports');
    }
};
