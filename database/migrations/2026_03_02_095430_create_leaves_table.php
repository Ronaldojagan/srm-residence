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
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('subscriptions');
            $table->foreignId('user_id')->constrained('users');
            $table->date('leave_date');                     // Must not be Sunday
            $table->string('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'cancelled'])->default('approved');
            $table->foreignId('approved_by')->nullable()->constrained('users'); // Admin user id
            $table->timestamps();

            $table->unique(['subscription_id', 'leave_date']); // One leave per day
            $table->index(['user_id', 'status']);
            $table->index('leave_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};
