<?php

namespace App\Models;

use App\Traits\ResolveRouteBinding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductImage extends Model
{
    use HasFactory, SoftDeletes, ResolveRouteBinding;

    protected $fillable = ['product_id', 'image_path', 'created_by'];

    protected $appends = ['image_url'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function getImageUrlAttribute()
    {
        if ($this->image_path == 'avatar.jpg') {
            return url('img/avatar.jpg');
        }
        return url('/storage/product/' . $this->image_path);
    }
}
