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
            if (!Schema::hasColumn('expenses', 'expense_month')) {
                $table->string('expense_month', 20)->nullable()->index()->after('expense_date');
            }
            if (!Schema::hasColumn('expenses', 'items')) {
                $table->json('items')->nullable()->after('notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (Schema::hasColumn('expenses', 'expense_month')) {
                $table->dropColumn('expense_month');
            }
            if (Schema::hasColumn('expenses', 'items')) {
                $table->dropColumn('items');
            }
        });
    }
};
