<?php

namespace App\Models;

use App\Traits\ResolveRouteBinding;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shop extends Model
{
    use HasFactory, SoftDeletes, ResolveRouteBinding;

    protected $fillable = [
        'user_id',
        'name',
        'image',
        'email',
        'address',
        'owner_name',
        'owner_phone',
        'latitude',
        'longitude',
        'commission_percentage',
        'deactivation_status',
        'approved',
        'rejection_reason',
        'approved_at',
        'rejected_at',
        'approved_by',
        'rejected_by',
        'status',
    ];

    protected $dates = ['approved_at', 'rejected_at'];
    protected $appends = ['image_url'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function changeRequests()
    {
        return $this->hasMany(ShopChangeRequest::class);
    }

    public function pendingChangeRequests()
    {
        return $this->hasMany(ShopChangeRequest::class)->where('status', 'pending');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getImageUrlAttribute()
    {
        if ($this->image == 'avatar.jpg') {
            return url('img/avatar.jpg');
        }
        return url('/storage/shop/' . $this->image);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payouts()
    {
        return $this->hasMany(Payout::class);
    }

    public function latestPayout()
    {
        return $this->hasOne(Payout::class)->latestOfMany();
    }

    public function deactivationHistory()
    {
        return $this->hasMany(ShopDeactivationHistory::class);
    }

    public function latestDeactivation()
    {
        return $this->hasOne(ShopDeactivationHistory::class)
            ->where('action', 'deactivated')
            ->latest();
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function ratings()
    {
        return $this->hasMany(ShopRating::class);
    }

    public function averageRating()
    {
        $avg = $this->ratings()->avg('rating');
        return number_format($avg, 2);
    }

    public function shopOrders()
    {
        return $this->hasMany(ShopOrder::class);
    }

    public function getHasPendingChangesAttribute()
    {
        return $this->pendingChangeRequests()->exists();
    }

    public function getLatestChangeRequestAttribute()
    {
        return $this->changeRequests()->latest()->first();
    }

    public function seller()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejector()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function scopeUnapproved($query)
    {
        return $query->where('approved', 0);
    }

    public function scopeApproved($query)
    {
        return $query->where('approved', 1);
    }

    public function scopeRejected($query)
    {
        return $query->where('approved', -1);
    }

    public function isUnapproved()
    {
        return $this->approved == 0;
    }

    public function isApproved()
    {
        return $this->approved == 1;
    }

    public function isRejected()
    {
        return $this->approved == -1;
    }
}
