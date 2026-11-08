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
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();
            $table->string('salary_month', 7); // e.g. "2026-11"
            $table->string('salary_title', 150)->default('Monthly Salary'); // e.g. "Monthly Owner Salary", "Director Remuneration"
            $table->string('recipient_name', 150); // e.g. "Jagan", "Ragavan"
            $table->unsignedBigInteger('user_id')->nullable()->index(); // optional link to users table
            $table->decimal('base_amount', 12, 2)->default(0);
            $table->decimal('bonus_incentive', 12, 2)->default(0);
            $table->decimal('deductions', 12, 2)->default(0);
            $table->decimal('net_salary', 12, 2)->default(0);
            $table->date('credit_date')->nullable();
            $table->string('payment_method', 50)->default('Bank Transfer'); // Bank Transfer, UPI, Cash, Cheque
            $table->string('credited_to_bank', 150)->nullable(); // e.g. "SBI Current A/C ***2248"
            $table->string('transaction_reference', 100)->nullable(); // UTR / Transaction ID
            $table->string('status', 30)->default('credited'); // credited, pending, partial
            $table->string('payslip_document')->nullable(); // uploaded receipt / bank slip / slip PDF
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['salary_month', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salaries');
    }
};
