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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            // $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            // $table->foreignId('shop_id')->nullable()->constrained('shops')->nullOnDelete();
            $table->string('replacement_order_id')->nullable();
            $table->string('product_name')->nullable();
            $table->string('product_image')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('quantity_unit')->nullable();
            $table->integer('quantity')->nullable();
            $table->text('description')->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->string('status', 50)->default('pending');
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('refund_status', 50)->nullable()->default('pending');
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->string('refund_id', 100)->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->text('return_reason')->nullable();
            $table->string('return_image')->nullable();
            $table->timestamp('return_requested_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->string('tracking_number', 100)->nullable();
            $table->string('courier_name', 100)->nullable();
            $table->string('shipment_id')->nullable();

            $table->boolean('return_verified_by_admin')->nullable();
            $table->timestamp('admin_verified_at')->nullable();
            $table->text('admin_verification_notes')->nullable();
            $table->boolean('shop_verified')->nullable();
            $table->timestamp('shop_verified_at')->nullable();
            $table->text('shop_verification_notes')->nullable();
            $table->string('product_condition', 50)->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            // $table->foreignId('verified_by_shop')->nullable()->constrained('shops')->nullOnDelete();

            $table->text('replace_reason')->nullable();
            $table->string('replace_image')->nullable();
            $table->timestamp('replace_requested_at')->nullable();
            $table->boolean('replace_verified_by_admin')->nullable();
            $table->timestamp('replace_admin_verified_at')->nullable();
            $table->text('replace_admin_verification_notes')->nullable();
            // $table->foreignId('new_product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('original_order_item_id')->nullable()->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['status']);
            $table->index(['refund_status']);
            $table->index(['cancelled_at']);
            $table->index(['shipment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
