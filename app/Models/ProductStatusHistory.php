<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'old_status',
        'new_status',
        'remarks',
        'changed_by'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
