<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanEmi extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'loan_id',
        'installment_number',
        'payment_date',
        'amount',
        'principal_component',
        'interest_component',
        'payment_method',
        'transaction_id',
        'status',
        'receipt',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'principal_component' => 'decimal:2',
        'interest_component' => 'decimal:2',
        'installment_number' => 'integer',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class, 'loan_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
