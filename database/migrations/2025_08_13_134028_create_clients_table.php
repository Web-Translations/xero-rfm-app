<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->char('contact_id', 36); // Xero ContactID (GUID)
            $t->string('name');
            $t->timestamps();
            $t->unique(['user_id','contact_id']);
            $t->index(['user_id','name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
