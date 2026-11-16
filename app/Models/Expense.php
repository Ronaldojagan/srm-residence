<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'expense_type',
        'room_id',
        'category',
        'title',
        'amount',
        'paid_amount',
        'payment_status',
        'expense_date',
        'expense_month',
        'payment_method',
        'paid_to',
        'receipt',
        'notes',
        'items',
        'status',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'expense_date' => 'date',
        'items' => 'array',
        'status' => 'boolean',
    ];

    protected $appends = [
        'due_amount',
        'balance_amount',
    ];

    public function getDueAmountAttribute()
    {
        $paid = (float)($this->attributes['paid_amount'] ?? 0);
        $total = (float)($this->attributes['amount'] ?? 0);
        return max(0, round($total - $paid, 2));
    }

    public function getBalanceAmountAttribute()
    {
        return $this->getDueAmountAttribute();
    }

    /**
     * Recalculate paid amount and payment status based on line items or single amount
     */
    public function syncPaymentStatus(bool $save = true): self
    {
        $total = (float)$this->amount;
        $items = $this->items;

        if (!empty($items) && is_array($items) && count($items) > 0) {
            $paid = 0.0;
            foreach ($items as $it) {
                if (!empty($it['is_paid'])) {
                    $paid += (float)($it['amount'] ?? 0);
                }
            }
            $paid = round(min($total, $paid), 2);
            $this->paid_amount = $paid;

            if ($paid >= $total && $total > 0) {
                $this->payment_status = 'paid';
                $this->status = true;
            } elseif ($paid > 0) {
                $this->payment_status = 'partially_paid';
                $this->status = false;
            } else {
                $this->payment_status = 'unpaid';
                $this->status = false;
            }
        } else {
            // Single entry
            if ($this->payment_status === 'paid' || $this->status) {
                $this->paid_amount = $total;
                $this->payment_status = 'paid';
                $this->status = true;
            } else {
                $this->paid_amount = 0.0;
                $this->payment_status = 'unpaid';
                $this->status = false;
            }
        }

        if ($save) {
            $this->save();
        }

        return $this;
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
