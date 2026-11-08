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
        'repayment_frequency',
        'lender_name',
        'account_number',
        'principal_amount',
        'interest_rate',
        'tenure_months',
        'already_paid_units',
        'already_paid_amount',
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
        'already_paid_amount' => 'decimal:2',
        'total_repaid' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'tenure_months' => 'integer',
        'already_paid_units' => 'integer',
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
     * Is this a weekly loan?
     */
    public function getIsWeeklyAttribute(): bool
    {
        return ($this->repayment_frequency ?? 'monthly') === 'weekly';
    }

    /**
     * Tenure unit label (Months or Weeks)
     */
    public function getTenureUnitAttribute(): string
    {
        return $this->is_weekly ? 'Weeks' : 'Months';
    }

    /**
     * Frequency label (Weekly or Monthly)
     */
    public function getFrequencyLabelAttribute(): string
    {
        return $this->is_weekly ? 'Weekly' : 'Monthly';
    }

    /**
     * Frequency badge HTML
     */
    public function getFrequencyBadgeAttribute(): string
    {
        if ($this->is_weekly) {
            return '<span class="badge bg-warning-subtle text-dark border border-warning font-11 px-2 py-0"><i class="fas fa-calendar-week me-1"></i>Weekly</span>';
        }
        return '<span class="badge bg-primary-subtle text-primary border border-primary font-11 px-2 py-0"><i class="fas fa-calendar-alt me-1"></i>Monthly</span>';
    }

    /**
     * Recalculate repaid and outstanding balance from EMI records and already paid units
     */
    public function recalculateBalance(): void
    {
        $paidEmisSum = (float) $this->emis()->where('status', 'paid')->sum('amount');
        
        $initialPaid = (float) $this->already_paid_amount;
        if ($initialPaid == 0 && (int)$this->already_paid_units > 0) {
            $initialPaid = (float) ($this->already_paid_units * $this->emi_amount);
            $this->already_paid_amount = $initialPaid;
        }

        $totalRepaid = $initialPaid + $paidEmisSum;
        $this->total_repaid = $totalRepaid;

        $totalPayable = (float) ($this->emi_amount * $this->tenure_months);
        if ($totalPayable < (float) $this->principal_amount) {
            $totalPayable = (float) $this->principal_amount;
        }

        $balance = max(0, $totalPayable - $totalRepaid);
        $this->outstanding_balance = $balance;

        if ($balance <= 0) {
            $this->status = 'closed';
        } elseif (empty($this->status)) {
            $this->status = 'active';
        }

        $this->save();
    }

    /**
     * Total paid installments count (Already Paid Units + Recorded EMIs)
     */
    public function getTotalPaidUnitsAttribute(): int
    {
        $recordedCount = $this->emis()->where('status', 'paid')->count();
        return (int) $this->already_paid_units + $recordedCount;
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
        if ($this->status !== 'active' || (float)$this->outstanding_balance <= 0) {
            return null;
        }

        $startDate = $this->start_date ? Carbon::parse($this->start_date) : Carbon::now();
        $paidCount = $this->total_paid_units;

        if ($this->is_weekly) {
            // Next due week date
            $nextDate = $startDate->copy()->addWeeks($paidCount);
            return $nextDate;
        } else {
            // Next due month date
            $dueDay = min((int) ($this->emi_day ?: 5), 28);
            $nextDate = $startDate->copy()->addMonths($paidCount)->day($dueDay);
            return $nextDate;
        }
    }
}
