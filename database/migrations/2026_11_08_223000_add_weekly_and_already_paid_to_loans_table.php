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
        Schema::table('loans', function (Blueprint $table) {
            if (!Schema::hasColumn('loans', 'repayment_frequency')) {
                $table->string('repayment_frequency', 20)->default('monthly')->after('loan_type'); // 'monthly', 'weekly'
            }
            if (!Schema::hasColumn('loans', 'already_paid_units')) {
                $table->integer('already_paid_units')->default(0)->after('tenure_months'); // number of months or weeks already paid
            }
            if (!Schema::hasColumn('loans', 'already_paid_amount')) {
                $table->decimal('already_paid_amount', 12, 2)->default(0)->after('already_paid_units'); // total amount already paid prior
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $cols = ['repayment_frequency', 'already_paid_units', 'already_paid_amount'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('loans', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
