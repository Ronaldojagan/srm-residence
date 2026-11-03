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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            // $table->foreignId('subscription_id')->constrained('subscriptions');
            // $table->foreignId('billing_id')->constrained('subscription_billings');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'upi', 'card', 'online'])->default('cash');
            $table->string('transaction_id')->nullable();
            $table->enum('status', ['pending', 'success', 'failed'])->default('success');
            $table->string('remarks')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users'); // Admin who recorded
            $table->timestamps();

            $table->index(['user_id', 'status']);
            // $table->index('billing_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
