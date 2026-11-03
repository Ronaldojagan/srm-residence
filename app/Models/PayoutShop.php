<?php

namespace App\Models;

use App\Traits\ResolveRouteBinding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayoutShop extends Model
{
    use HasFactory, ResolveRouteBinding;

    const STATUS_GENERATED = 'generated';
    const STATUS_PROCESSING = 'processing';
    const STATUS_PAID = 'paid';
    const STATUS_FAILED = 'failed';

    protected $fillable = [
        'payout_id',
        'shop_id',
        'amount',
        'commission',
        'commission_percentage',
        'payable_amount',
        'status',
        'payment_reference',
        'paid_at',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'commission' => 'decimal:2',
        'payable_amount' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'paid_at' => 'datetime'
    ];

    public function payout()
    {
        return $this->belongsTo(Payout::class);
    }

    public function orderItems()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function calculateCommission()
    {
        if ($this->commission_percentage > 0) {
            return $this->amount * ($this->commission_percentage / 100);
        }

        if ($this->shop && $this->shop->commission_percentage > 0) {
            return $this->amount * ($this->shop->commission_percentage / 100);
        }

        return 0;
    }

    public function calculatePayableAmount()
    {
        $commission = $this->commission ?? $this->calculateCommission();
        return $this->amount - $commission;
    }

    public function updateCalculations()
    {
        $this->commission = $this->calculateCommission();
        $this->payable_amount = $this->calculatePayableAmount();
        $this->save();
    }

    public function markAsPaid($reference = null)
    {
        $this->update([
            'status' => self::STATUS_PAID,
            'payment_reference' => $reference,
            'paid_at' => now()
        ]);
    }

    public function markAsFailed($notes = null)
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'notes' => $notes
        ]);
    }
}
