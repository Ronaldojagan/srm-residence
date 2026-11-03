<?php

namespace App\Models;

use App\Traits\ResolveRouteBinding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends Model
{
    use HasFactory, SoftDeletes, ResolveRouteBinding;

    protected $fillable = [
        'name',
        'image',
        'status'
    ];

    protected $appends = ['image_url'];

    protected $casts = [
        'status' => 'boolean'
    ];
    public function getImageUrlAttribute()
    {
        if ($this->image == 'avatar.jpg') {
            return url('img/avatar.jpg');
        }
        return url('/storage/brand/' . $this->image);
    }
}
