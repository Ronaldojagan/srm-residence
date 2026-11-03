<?php

namespace App\Models;

use App\Traits\ResolveRouteBinding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payout extends Model
{
    use HasFactory, ResolveRouteBinding;

    protected $fillable = [
        'start_date',
        'end_date',
        'paid_date',
        'status',
        'notes',
        'admin_id'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'paid_date' => 'datetime'
    ];

    // Status constants
    const STATUS_GENERATED = 'generated';
    const STATUS_PROCESSING = 'processing';
    const STATUS_PAID = 'paid';
    const STATUS_FAILED = 'failed';

    public function payoutShops(): HasMany
    {
        return $this->hasMany(PayoutShop::class);
    }

    // Scopes
    public function scopeGenerated($query)
    {
        return $query->where('status', self::STATUS_GENERATED);
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->where('start_date', '>=', $startDate)
                    ->where('end_date', '<=', $endDate);
    }

    // Accessors
    public function getTotalAmountAttribute()
    {
        return $this->payoutShops()->sum('amount');
    }

    public function getTotalCommissionAttribute()
    {
        return $this->payoutShops()->sum('commission');
    }

    public function getTotalPayableAmountAttribute()
    {
        return $this->payoutShops()->sum('payable_amount');
    }

    public function getShopCountAttribute()
    {
        return $this->payoutShops()->count();
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            self::STATUS_GENERATED => '<span class="badge bg-warning">Generated</span>',
            self::STATUS_PROCESSING => '<span class="badge bg-info">Processing</span>',
            self::STATUS_PAID => '<span class="badge bg-success">Paid</span>',
            self::STATUS_FAILED => '<span class="badge bg-danger">Failed</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    // Methods
    public function markAsProcessing(): bool
    {
        return $this->update(['status' => self::STATUS_PROCESSING]);
    }

    public function markAsPaid(?string $notes = null): bool
    {
        return $this->update([
            'status' => self::STATUS_PAID,
            'paid_date' => now(),
            'notes' => $notes
        ]);
    }

    public function markAsFailed(?string $notes = null): bool
    {
        return $this->update([
            'status' => self::STATUS_FAILED,
            'notes' => $notes
        ]);
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isGenerated(): bool
    {
        return $this->status === self::STATUS_GENERATED;
    }

    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }
}
