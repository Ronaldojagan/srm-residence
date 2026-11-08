<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Salary extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'salary_month',
        'salary_title',
        'recipient_name',
        'user_id',
        'base_amount',
        'bonus_incentive',
        'deductions',
        'net_salary',
        'credit_date',
        'payment_method',
        'credited_to_bank',
        'transaction_reference',
        'status',
        'payslip_document',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'base_amount' => 'decimal:2',
        'bonus_incentive' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'credit_date' => 'date',
    ];

    /**
     * Creator relationship
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * User profile relationship (if linked)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Formatted month name, e.g. "November 2026"
     */
    public function getFormattedSalaryMonthAttribute(): string
    {
        if (empty($this->salary_month)) {
            return '-';
        }
        try {
            return Carbon::createFromFormat('Y-m', $this->salary_month)->format('F Y');
        } catch (\Throwable $e) {
            return $this->salary_month;
        }
    }

    /**
     * Auto calculate net salary on saving
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $base = (float) $model->base_amount;
            $bonus = (float) $model->bonus_incentive;
            $deductions = (float) $model->deductions;
            $model->net_salary = max(0, $base + $bonus - $deductions);
        });
    }
}
