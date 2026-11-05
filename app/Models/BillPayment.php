<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'payment_number',
        'bill_id',
        'tenant_id',
        'amount',
        'payment_date',
        'payment_method',
        'transaction_id',
        'receipt',
        'notes',
        'received_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function bill()
    {
        return $this->belongsTo(Bill::class, 'bill_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public static function generatePaymentNumber(): string
    {
        $prefix = 'RCP-' . date('Ym') . '-';
        $latest = static::withTrashed()
            ->where('payment_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $num = (int) substr($latest->payment_number, -4);
            $next = str_pad($num + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $next = '0001';
        }

        return $prefix . $next;
    }

    protected static function booted()
    {
        static::saved(function ($payment) {
            if ($payment->bill) {
                $payment->bill->recalculatePaymentStatus();
            }
        });

        static::deleted(function ($payment) {
            if ($payment->bill) {
                $payment->bill->recalculatePaymentStatus();
            }
        });
    }
}
