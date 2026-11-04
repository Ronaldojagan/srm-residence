<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_number')->unique();
            $table->string('floor')->default('1st Floor');
            $table->string('room_type')->default('Single Deluxe');
            $table->integer('capacity')->default(1);
            $table->decimal('monthly_rent', 10, 2)->default(8000.00);
            $table->decimal('security_deposit', 10, 2)->default(10000.00);
            $table->enum('occupancy_status', ['available', 'occupied', 'maintenance'])->default('available');
            $table->text('amenities')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
