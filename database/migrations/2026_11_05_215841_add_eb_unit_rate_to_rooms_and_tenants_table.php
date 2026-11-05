<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->decimal('eb_unit_rate', 8, 2)->default(10.00)->after('monthly_rent');
            if (Schema::hasColumn('rooms', 'eb_charges')) {
                $table->dropColumn('eb_charges');
            }
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->decimal('eb_unit_rate', 8, 2)->default(10.00)->after('monthly_rent');
            if (Schema::hasColumn('tenants', 'eb_charges')) {
                $table->dropColumn('eb_charges');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('eb_unit_rate');
            $table->decimal('eb_charges', 10, 2)->default(0.00)->after('monthly_rent');
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('eb_unit_rate');
            $table->decimal('eb_charges', 10, 2)->default(0.00)->after('monthly_rent');
        });
    }
};
