<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'loan_title',
        'loan_type',
        'lender_name',
        'account_number',
        'principal_amount',
        'interest_rate',
        'tenure_months',
        'emi_amount',
        'emi_day',
        'start_date',
        'end_date',
        'total_repaid',
        'outstanding_balance',
        'status',
        'bank_account_debited',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'principal_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'emi_amount' => 'decimal:2',
        'total_repaid' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'tenure_months' => 'integer',
        'emi_day' => 'integer',
    ];

    public function emis()
    {
        return $this->hasMany(LoanEmi::class, 'loan_id')->orderBy('installment_number', 'asc');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Recalculate repaid and outstanding balance from EMI records
     */
    public function recalculateBalance(): void
    {
        $paidSum = (float) $this->emis()->where('status', 'paid')->sum('amount');
        $this->total_repaid = $paidSum;
        
        $totalPayable = (float) ($this->emi_amount * $this->tenure_months);
        if ($totalPayable < (float) $this->principal_amount) {
            $totalPayable = (float) $this->principal_amount;
        }

        $balance = max(0, $totalPayable - $paidSum);
        $this->outstanding_balance = $balance;

        if ($balance <= 0 && $this->status === 'active') {
            $this->status = 'closed';
        } elseif ($balance > 0 && $this->status === 'closed') {
            $this->status = 'active';
        }

        $this->save();
    }

    /**
     * Get percentage repaid
     */
    public function getProgressPercentageAttribute(): float
    {
        $totalPayable = (float) ($this->emi_amount * $this->tenure_months);
        if ($totalPayable <= 0) {
            $totalPayable = (float) $this->principal_amount;
        }
        if ($totalPayable <= 0) return 0;

        return min(100, round(((float) $this->total_repaid / $totalPayable) * 100, 1));
    }

    /**
     * Get next EMI due date
     */
    public function getNextDueDateAttribute(): ?Carbon
    {
        if ($this->status !== 'active') return null;

        $now = Carbon::now();
        $dueDay = (int) ($this->emi_day ?: 5);

        try {
            $candidate = Carbon::create($now->year, $now->month, $dueDay);
            if ($candidate->isPast()) {
                $candidate = $candidate->addMonth();
            }
            return $candidate;
        } catch (\Exception $e) {
            return $now->copy()->addDays(15);
        }
    }
}
