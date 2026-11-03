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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('delivery_address_id')->constrained()->onDelete('cascade');
            $table->foreignId('precheckout_id')->nullable()->constrained()->onDelete('set null');
            $table->string('order_no')->unique();
            $table->string('order_type')->nullable();
            $table->string('pay_type')->default('Online')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->unsignedBigInteger('cancelled_by')->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->string('status')->default('pending');

            $table->boolean('is_replacement_order')->default(false)->index();
            $table->foreignId('original_order_id')->nullable()->nullOnDelete();
            $table->foreignId('original_order_item_id')->nullable()->nullOnDelete();
            $table->text('replacement_notes')->nullable();

            $table->boolean('has_shipments')->default(false);
            $table->boolean('all_items_delivered')->default(false);
            $table->timestamp('delivery_completed_at')->nullable();
            $table->integer('shipments_count')->default(0);
            $table->integer('delivered_shipments_count')->default(0);

            $table->date('date');
            $table->string('completion_code')->nullable();
            $table->string('invoice_no')->unique();
            $table->timestamp('completed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['has_shipments']);
            $table->index(['all_items_delivered']);
            $table->index(['delivery_completed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
