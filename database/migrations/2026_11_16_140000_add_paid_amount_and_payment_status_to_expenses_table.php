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
        Schema::table('expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('expenses', 'paid_amount')) {
                $table->decimal('paid_amount', 10, 2)->default(0)->after('amount');
            }
            if (!Schema::hasColumn('expenses', 'payment_status')) {
                $table->string('payment_status', 20)->default('unpaid')->index()->after('paid_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (Schema::hasColumn('expenses', 'paid_amount')) {
                $table->dropColumn('paid_amount');
            }
            if (Schema::hasColumn('expenses', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
        });
    }
};
