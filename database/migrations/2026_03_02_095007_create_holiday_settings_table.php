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
        Schema::create('holiday_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['weekly', 'custom'])->default('weekly');
            $table->tinyInteger('day_of_week')->nullable(); // 0=Sun,1=Mon,...,6=Sat
            $table->date('holiday_date')->nullable();       // for custom/festival dates
            $table->string('reason')->nullable();           // "Sunday Holiday", "Diwali"
            $table->boolean('is_protected')->default(false); // Sunday row = true, cannot delete
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['type', 'is_active']);
            $table->index('holiday_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holiday_settings');
    }
};
