<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'eb_start_reading')) {
                $table->decimal('eb_start_reading', 10, 2)->default(0.00)->after('eb_unit_rate');
            }
        });

        Schema::table('bills', function (Blueprint $table) {
            if (!Schema::hasColumn('bills', 'eb_previous_reading')) {
                $table->decimal('eb_previous_reading', 10, 2)->nullable()->after('amount');
            }
            if (!Schema::hasColumn('bills', 'eb_current_reading')) {
                $table->decimal('eb_current_reading', 10, 2)->nullable()->after('eb_previous_reading');
            }
            if (!Schema::hasColumn('bills', 'eb_consumed_units')) {
                $table->decimal('eb_consumed_units', 10, 2)->nullable()->after('eb_current_reading');
            }
            if (!Schema::hasColumn('bills', 'eb_unit_rate')) {
                $table->decimal('eb_unit_rate', 8, 2)->nullable()->after('eb_consumed_units');
            }
            if (!Schema::hasColumn('bills', 'eb_amount')) {
                $table->decimal('eb_amount', 10, 2)->nullable()->after('eb_unit_rate');
            }
            if (!Schema::hasColumn('bills', 'items')) {
                $table->json('items')->nullable()->after('notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (Schema::hasColumn('tenants', 'eb_start_reading')) {
                $table->dropColumn('eb_start_reading');
            }
        });

        Schema::table('bills', function (Blueprint $table) {
            $cols = ['eb_previous_reading', 'eb_current_reading', 'eb_consumed_units', 'eb_unit_rate', 'eb_amount', 'items'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('bills', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

