<?php

namespace App\Models;

use App\Traits\ResolveRouteBinding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopChangeRequest extends Model
{
    use HasFactory, ResolveRouteBinding;

    protected $fillable = [
        'shop_id',
        'user_id',
        'changes',
        'temp_image',
        'rejection_reason',
        'status',
        'reviewed_by',
        'reviewed_at',
        'old_data',
        'approved_data',
        'remarks',
        'action_taken_by'
    ];

    protected $casts = [
        'changes' => 'array',
        'old_data' => 'array',
        'approved_data' => 'array',
        'reviewed_at' => 'datetime'
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function actionPerformer()
    {
        return $this->belongsTo(User::class, 'action_taken_by');
    }

    public function getStatusBadgeAttribute()
    {
        $badgeClasses = [
            'pending' => 'badge bg-warning',
            'approved' => 'badge bg-success',
            'rejected' => 'badge bg-danger'
        ];

        return '<span class="' . ($badgeClasses[$this->status] ?? 'badge bg-secondary') . '">' .
               ucfirst($this->status) . '</span>';
    }

    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected()
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function getFormattedChanges()
    {
        $changes = $this->changes ?? [];
        $formatted = [];

        foreach ($changes as $field => $value) {
            $fieldName = str_replace('_', ' ', ucfirst($field));
            $formatted[$fieldName] = $value;
        }

        return $formatted;
    }

    public function applyChanges()
    {
        $shop = $this->shop;
        $changes = $this->changes ?? [];

        if (isset($changes['image']) && $this->temp_image) {
            $tempPath = storage_path('app/public/temp/shop-updates/' . $this->temp_image);
            $permanentPath = storage_path('app/public/shop/' . $changes['image']);

            if (file_exists($tempPath)) {
                $oldImage = $shop->image;
                if ($oldImage && $oldImage !== 'avatar.jpg') {
                    $oldPath = storage_path('app/public/shop/' . $oldImage);
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }

                rename($tempPath, $permanentPath);

                $shop->image = $changes['image'];
            }
        }

        if (isset($changes['name'])) {
            $shop->name = $changes['name'];
        }

        if (isset($changes['address'])) {
            $shop->address = $changes['address'];
        }

        $shop->save();
    }

    public function cleanupTempImage()
    {
        if ($this->temp_image) {
            $tempPath = storage_path('app/public/temp/shop-updates/' . $this->temp_image);
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }
    }
}
