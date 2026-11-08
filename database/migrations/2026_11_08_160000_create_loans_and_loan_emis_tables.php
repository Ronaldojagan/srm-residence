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
        if (!Schema::hasTable('loans')) {
            Schema::create('loans', function (Blueprint $table) {
                $table->id();
                $table->string('loan_title'); // e.g. Property Expansion Loan, Room 12 AC EMI, Washing Machine EMI
                $table->string('loan_type')->default('business'); // business, personal, property, appliance, vehicle, hand_loan, other
                $table->string('lender_name'); // Bank, NBFC or Financier (e.g. HDFC Bank, SBI, Bajaj Finserv, Private)
                $table->string('account_number')->nullable(); // Loan Account / Ref Number
                $table->decimal('principal_amount', 12, 2); // Total loan amount borrowed
                $table->decimal('interest_rate', 5, 2)->nullable()->default(0); // Annual interest %
                $table->integer('tenure_months')->default(12); // Duration in months
                $table->decimal('emi_amount', 10, 2); // Monthly installment amount
                $table->tinyInteger('emi_day')->default(5); // Due day of month (1-31, e.g. 5th)
                $table->date('start_date');
                $table->date('end_date')->nullable();
                $table->decimal('total_repaid', 12, 2)->default(0); // Sum of paid EMIs
                $table->decimal('outstanding_balance', 12, 2)->default(0); // Remaining amount
                $table->string('status')->default('active'); // active, closed, overdue
                $table->string('bank_account_debited')->nullable(); // Account debited from
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('loan_emis')) {
            Schema::create('loan_emis', function (Blueprint $table) {
                $table->id();
                $table->foreignId('loan_id')->constrained('loans')->cascadeOnDelete();
                $table->integer('installment_number')->default(1); // Installment 1, 2, 3...
                $table->date('payment_date');
                $table->decimal('amount', 10, 2); // Amount paid
                $table->decimal('principal_component', 10, 2)->nullable()->default(0);
                $table->decimal('interest_component', 10, 2)->nullable()->default(0);
                $table->string('payment_method')->default('Bank Transfer'); // Bank Transfer, Auto-Debit / NACH, UPI, Cash, Cheque
                $table->string('transaction_id')->nullable(); // UTR / Ref #
                $table->string('status')->default('paid'); // paid, pending, skipped
                $table->string('receipt')->nullable(); // Slip / attachment
                $table->text('notes')->nullable();
                $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
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
        Schema::dropIfExists('loan_emis');
        Schema::dropIfExists('loans');
    }
};
