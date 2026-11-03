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
        Schema::create('subscriptions_billings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('subscriptions');
            $table->foreignId('user_id')->constrained('users');
            $table->string('billing_month', 7);             // "2024-06" (YYYY-MM)
            $table->integer('base_days');                   // Workable days (Mon–Sat) in month
            $table->integer('leaves_taken')->default(0);    // Approved leaves in month
            $table->integer('carried_forward_days')->default(0); // Carried in from prev month
            $table->integer('total_billable_days');         // base_days - leaves + carried_forward
            $table->decimal('price_per_day', 8, 2)->default(40.00);
            $table->decimal('amount_due', 10, 2);           // total_billable_days * price_per_day
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->enum('payment_status', ['pending', 'partial', 'paid'])->default('pending');
            $table->date('due_date')->nullable();
            $table->date('paid_date')->nullable();
            $table->timestamps();

            $table->unique(['subscription_id', 'billing_month']);
            $table->index(['user_id', 'payment_status']);
            $table->index('billing_month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions_billings');
    }
};
