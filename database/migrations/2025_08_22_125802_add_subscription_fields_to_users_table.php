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
        Schema::table('users', function (Blueprint $table) {
            $table->string('subscription_plan')->default('free')->after('email');
            $table->string('gocardless_subscription_id')->nullable()->after('subscription_plan');
            $table->string('subscription_status')->default('active')->after('gocardless_subscription_id');
            $table->timestamp('subscription_ends_at')->nullable()->after('subscription_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'subscription_plan',
                'gocardless_subscription_id', 
                'subscription_status',
                'subscription_ends_at'
            ]);
        });
    }
};
