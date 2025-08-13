<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('xero_connections', function (Blueprint $t) {
            $t->string('org_name')->nullable()->after('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::table('xero_connections', function (Blueprint $t) {
            $t->dropColumn('org_name');
        });
    }
};

