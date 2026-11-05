<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->decimal('eb_charges', 10, 2)->default(0.00)->after('monthly_rent');
            $table->decimal('maintenance_charges', 10, 2)->default(0.00)->after('eb_charges');
            $table->decimal('other_charges', 10, 2)->default(0.00)->after('maintenance_charges');
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->decimal('eb_charges', 10, 2)->default(0.00)->after('monthly_rent');
            $table->decimal('maintenance_charges', 10, 2)->default(0.00)->after('eb_charges');
            $table->decimal('other_charges', 10, 2)->default(0.00)->after('maintenance_charges');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['eb_charges', 'maintenance_charges', 'other_charges']);
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['eb_charges', 'maintenance_charges', 'other_charges']);
        });
    }
};
