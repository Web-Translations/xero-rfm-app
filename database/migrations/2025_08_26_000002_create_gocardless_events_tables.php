<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gocardless_subscription_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('subscription_id')->index();
            $table->string('event_id')->unique();
            $table->string('action');
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('gocardless_payment_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('payment_id')->index();
            $table->string('event_id')->unique();
            $table->string('status');
            $table->date('charge_date')->nullable();
            $table->integer('amount')->nullable();
            $table->string('currency', 3)->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gocardless_payment_events');
        Schema::dropIfExists('gocardless_subscription_events');
    }
};


