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
        'expense_date' => 'date',
        'items' => 'array',
        'status' => 'boolean',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
