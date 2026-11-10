<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalEntry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'entry_number',
        'entry_date',
        'entry_type',
        'category',
        'title',
        'amount',
        'payment_method',
        'account_name',
        'reference_no',
        'attachment',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'entry_date' => 'date',
    ];

    /**
     * Relationship with Creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Determine if entry is an inflow/credit or outflow/debit
     */
    public function getIsCreditAttribute(): bool
    {
        return in_array($this->entry_type, ['income', 'capital']);
    }

    /**
     * Generate Next Unique Voucher Number
     * e.g. JV-202611-0001
     */
    public static function generateEntryNumber(): string
    {
        $prefix = 'JV-' . date('Ym') . '-';
        $latest = static::withTrashed()
            ->where('entry_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $num = (int) substr($latest->entry_number, -4);
            $next = str_pad($num + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $next = '0001';
        }

        return $prefix . $next;
    }
}
