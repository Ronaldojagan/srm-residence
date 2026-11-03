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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('subscription_id')->constrained('subscriptions');
            $table->foreignId('user_id')->constrained('users');         // Customer
            $table->foreignId('delivery_boy_id')->nullable()->constrained('users'); // Delivery role
            $table->foreignId('delivery_address_id')->constrained('delivery_addresses');
            $table->date('delivery_date');
            $table->enum('status', [
                'pending',
                'assigned',
                'out_for_delivery',
                'delivered',
                'skipped',
                'on_leave',
                'holiday'
            ])->default('pending');
            $table->timestamp('delivered_at')->nullable();
            $table->string('delivery_note')->nullable();
            $table->string('skip_reason')->nullable();
            $table->timestamps();

            // $table->unique(['subscription_id', 'delivery_date']);
            $table->index(['delivery_boy_id', 'delivery_date']);
            $table->index(['delivery_date', 'status']);
            $table->index(['user_id', 'delivery_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
