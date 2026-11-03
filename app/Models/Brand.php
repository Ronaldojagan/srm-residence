<?php

namespace App\Models;

use App\Traits\ResolveRouteBinding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use HasFactory, SoftDeletes, ResolveRouteBinding;

    protected $fillable = [
        'name',
        'image',
        'sort_order',
        'status'
    ];
    protected $appends = ['image_url'];

    public function product()
    {
        return $this->hasMany(Product::class);
    }
    public function getImageUrlAttribute()
    {
        if ($this->image == 'avatar.jpg') {
            return url('img/avatar.jpg');
        }
        return url('/storage/brand/' . $this->image);
    }
}
