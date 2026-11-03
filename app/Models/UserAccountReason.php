<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAccountReason extends Model
{
    use HasFactory;

    protected $fillable = ['reason', 'is_show'];

    protected $casts = [
        'is_show' => 'boolean'
    ];
}
