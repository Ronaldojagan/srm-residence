<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopDeactivationHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'action_by',
        'reason',
        'action'
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function actionBy()
    {
        return $this->belongsTo(User::class, 'action_by');
    }
}
