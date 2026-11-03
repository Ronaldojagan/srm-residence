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
        Schema::create('delivery_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_boy_id')->constrained('users');
            $table->date('assignment_date');
            $table->integer('total_assigned')->default(0);
            $table->integer('total_delivered')->default(0);
            $table->integer('total_skipped')->default(0);
            $table->integer('total_on_leave')->default(0);
            $table->timestamps();

            $table->unique(['delivery_boy_id', 'assignment_date']);
            $table->index('assignment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_assignments');
    }
};
