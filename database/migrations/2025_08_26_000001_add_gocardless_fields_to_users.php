<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('gocardless_mandate_id')->nullable()->after('gocardless_subscription_id');
            $table->string('gc_last_completed_flow_id')->nullable()->after('gocardless_mandate_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['gocardless_mandate_id', 'gc_last_completed_flow_id']);
        });
    }
};


