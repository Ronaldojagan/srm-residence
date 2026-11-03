<?php

namespace App\Models;

use App\Traits\ResolveRouteBinding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use HasFactory, SoftDeletes, ResolveRouteBinding;

    protected $fillable=[
        'name',
        'code',
        'amount',
        'start_date',
        'expire_date',
        'status',
        'min_purchase_amount',
        'usage_limit',
        'used_count',
        'image',
        'description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expire_date' => 'date',
        'status' => 'boolean',
        'amount' => 'decimal:2',
        'min_purchase_amount' => 'decimal:2',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
    ];

    public function canBeUsed()
    {
        if (!$this->status) {
            return false;
        }

        if ($this->isExpired()) {
            return false;
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    public function isExpired()
    {
        return now()->greaterThan($this->expire_date);
    }

    public function incrementUsage()
    {
        $this->increment('used_count');
    }

    public function decrementUsage()
    {
        if ($this->used_count > 0) {
            $this->decrement('used_count');
        }
    }


    public function isUsedByUser($userId)
    {
        return Order::where('user_id', $userId)
            ->where('coupon_id', $this->id)
            ->exists();
    }
}
