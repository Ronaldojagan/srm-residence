<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('category', 100)->nullable()->default(null)->change();
            $table->string('payment_method', 50)->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('category', 100)->nullable(false)->change();
            $table->string('payment_method', 50)->default('Cash')->change();
        });
    }
};
