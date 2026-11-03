<?php

namespace App\Models;

use App\Traits\ResolveRouteBinding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubCategory extends Model
{
    use HasFactory, SoftDeletes, ResolveRouteBinding;

    protected $fillable = [
        'category_id',
        'name',
        'image',
        'sort_order',
        'status',
    ];
    protected $appends = ['image_url'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function banners()
    {
        return $this->hasMany(Banner::class);
    }

     public function product()
    {
        return $this->hasMany(Product::class);
    }

    public function getImageUrlAttribute()
    {
        if ($this->image == 'avatar.jpg') {
            return url('img/avatar.jpg');
        }
        return url('/storage/sub-category/' . $this->image);
    }
}
