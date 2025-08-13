<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('xero_connections', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->unique()->constrained()->cascadeOnDelete(); // 1 org per user
            $t->string('tenant_id');
            $t->text('access_token');
            $t->text('refresh_token');
            $t->timestamp('expires_at');
            $t->timestamps();
            $t->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('xero_connections');
    }
};
