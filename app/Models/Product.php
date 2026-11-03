<?php

namespace App\Models;

use App\Traits\ResolveRouteBinding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes, ResolveRouteBinding;

    protected $fillable = [
        'sub_category_id',
        'brand_id',
        'shop_id',
        'product_no',
        'name',
        'category_id',
        'image',
        'product_stock',
        'amount',
        'discount_amount',
        'discount_percentage',
        'max_product_stock_alert',
        'description',
        'created_by',
        'approved',
        'status',
        'rejection_reason',
        'keywords',
        'quantity',
        'unit_type',
        'additional_info',
        'youtube_videos'
    ];

    protected $appends = ['image_url', 'is_liked', 'full_images', 'available_stock'];

    protected $casts = [
        'additional_info' => 'array',
        'amount' => 'integer',
        'discount_amount' => 'integer',
        'discount_percentage' => 'integer',
        'product_stock' => 'integer',
    ];

    const APPROVAL_PENDING = -1;
    const APPROVAL_APPROVED = 1;
    const APPROVAL_REJECTED = 2;

    const STATUS_DEACTIVATED = 0;
    const STATUS_ACTIVE = 1;

    public function scopeSearch($query, $search)
    {
        if (isset($search)) {
            $query->orWhere('name', 'LIKE', '%' . $search . '%')
                ->orWhere('product_no', 'LIKE', '%' . $search . '%')
                ->orWhere('description', 'LIKE', '%' . $search . '%')
                ->orWhere('keywords', 'LIKE', '%' . $search . '%')
                ->orWhere('discount_amount', 'LIKE', '%' . $search . '%');
        }
    }

    public function getApprovalStatusAttribute()
    {
        switch ($this->approved) {
            case self::APPROVAL_PENDING:
                return 'Pending Review';
            case self::APPROVAL_APPROVED:
                return 'Approved';
            case self::APPROVAL_REJECTED:
                return 'Rejected';
            default:
                return 'Unknown';
        }
    }

    public function scopeActiveShop($query)
    {
        return $query->whereHas('shop', function($q) {
            $q->where('status', true)->where('approved', 1);
        });
    }

    public function scopeActiveCategory($query)
    {
        return $query->whereHas('category', function($q) {
            $q->where('status', true);
        });
    }

    public function scopeActiveSubCategory($query)
    {
        return $query->whereHas('subCategory', function($q) {
            $q->where('status', true);
        });
    }

    public function getAvailableStockAttribute()
    {
        return $this->product_stock > 0;
    }

    public function scopeApprovedWithShop($query)
    {
        return $query->where('approved', true)
            ->where('status', true)
            ->whereNotNull('shop_id')
            ->where('product_stock', '>', 0)
            ->activeShop()
            ->activeCategory()
            ->activeSubCategory();
    }

    public function getStatusTextAttribute()
    {
        return $this->status == self::STATUS_ACTIVE ? 'Active' : 'Deactivated';
    }

    public function getIsActiveAttribute()
    {
        return $this->status == self::STATUS_ACTIVE;
    }

    public function getIsApprovedAttribute()
    {
        return $this->approved == self::APPROVAL_APPROVED;
    }

    public function getIsPendingAttribute()
    {
        return $this->approved == self::APPROVAL_PENDING;
    }

    public function getIsRejectedAttribute()
    {
        return $this->approved == self::APPROVAL_REJECTED;
    }

    public function scopeApproved($query)
    {
        return $query->where('approved', self::APPROVAL_APPROVED);
    }

    public function scopePending($query)
    {
        return $query->where('approved', self::APPROVAL_PENDING);
    }

    public function scopeRejected($query)
    {
        return $query->where('approved', self::APPROVAL_REJECTED);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeDeactivated($query)
    {
        return $query->where('status', self::STATUS_DEACTIVATED);
    }

    public function scopeApprovedAndActive($query)
    {
        return $query->where('approved', self::APPROVAL_APPROVED)
                    ->where('status', self::STATUS_ACTIVE);
    }

    public function scopeInStock($query)
    {
        return $query->where('product_stock', '>', 0);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class, 'sub_category_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }

    public function additionalImages()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function getImageUrlAttribute()
    {
        if ($this->image == 'avatar.jpg') {
            return url('img/avatar.jpg');
        }
        return url('/storage/product/' . $this->image);
    }

    public function pendingUpdateRequests()
    {
        return $this->hasMany(ProductUpdateRequest::class)
            ->where('status', 'pending')
            ->latest();
    }

    public function getLatestPendingRequests()
    {
        return $this->pendingUpdateRequests()
            ->selectRaw('DISTINCT ON (type) *')
            ->orderBy('type')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getHasPendingUpdatesAttribute()
    {
        return $this->pendingUpdateRequests()->count() > 0;
    }

    public function getOverallStatusAttribute()
    {
        if ($this->approved === self::APPROVAL_PENDING) {
            return 'Pending Review';
        } elseif ($this->approved === self::APPROVAL_REJECTED) {
            return 'Rejected';
        } elseif ($this->has_pending_updates) {
            return 'Update Pending';
        } else {
            return 'Active';
        }
    }

    public function getFullImagesAttribute()
    {
        $images = [];

        $images[] = $this->image_url;

        foreach ($this->additionalImages as $img) {
            $images[] = $img->image_url;
        }

        return $images;
    }

    public function getIsLikedAttribute()
    {
        $userId = auth()->user()->id;
        return Wishlist::where('user_id', $userId)
            ->where('product_id', $this->id)
            ->exists();
    }

    public function averageRating()
    {
        $avg = $this->feedbacks()->avg('rating');
        return number_format($avg, 2);
    }

    public function likes()
    {
        return $this->hasMany(Wishlist::class, 'product_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'product_id');
    }

    public function statusHistory()
    {
        return $this->hasMany(ProductStatusHistory::class)->orderBy('created_at', 'desc');
    }

}
