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
        if (!Schema::hasTable('journal_entries')) {
            Schema::create('journal_entries', function (Blueprint $table) {
                $table->id();
                $table->string('entry_number', 50)->unique()->index(); // e.g. "JV-202611-0001"
                $table->date('entry_date')->index();
                $table->string('entry_type', 30)->default('income')->index(); // income, expense, capital, drawing, transfer
                $table->string('category', 100)->default('General'); // e.g. "Capital Introduced", "Owner Drawings", "Direct Hostel Income", "Bank Interest", "Bank Charges", "Scrap Sale", "Other"
                $table->string('title', 200);
                $table->decimal('amount', 12, 2)->default(0);
                $table->string('payment_method', 50)->default('Cash'); // Cash, UPI, Bank Transfer, Cheque, Card
                $table->string('account_name', 100)->nullable(); // e.g. "Cash in Hand", "SBI Current A/C", "Indian Bank A/C"
                $table->string('reference_no', 100)->nullable(); // UTR / Cheque # / Ref ID
                $table->string('attachment')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
