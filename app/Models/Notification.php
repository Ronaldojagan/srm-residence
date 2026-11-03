<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at'
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $appends = ['is_read', 'time_ago'];

    const TYPE_ORDER_CONFIRMATION = 'order_confirmation';
    const TYPE_WALLET_TOPUP = 'wallet_topup';
    const TYPE_NEW_ORDER = 'new_order';
    const TYPE_ORDER_STATUS_UPDATE = 'order_status_update';
    const TYPE_PAYMENT_SUCCESS = 'payment_success';
    const TYPE_SHIPMENT_UPDATE = 'shipment_update';
    const TYPE_LOW_STOCK = 'low_stock';
    const TYPE_PROMOTION = 'promotion';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getIsReadAttribute()
    {
        return !is_null($this->read_at);
    }

    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getFormattedDateAttribute()
    {
        return $this->created_at->format('d M Y, h:i A');
    }

    public function markAsRead()
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
        return $this;
    }

    public function markAsUnread()
    {
        $this->update(['read_at' => null]);
        return $this;
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRecent($query, $limit = 20)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    public function scopeUnreadCount($query, $userId = null)
    {
        $query = $query->whereNull('read_at');

        if ($userId) {
            $query = $query->where('user_id', $userId);
        }

        return $query->count();
    }

    public function getNotificationTypeLabelAttribute()
    {
        $labels = [
            self::TYPE_ORDER_CONFIRMATION => 'Order Confirmation',
            self::TYPE_WALLET_TOPUP => 'Wallet Topup',
            self::TYPE_NEW_ORDER => 'New Order',
            self::TYPE_ORDER_STATUS_UPDATE => 'Order Update',
            self::TYPE_PAYMENT_SUCCESS => 'Payment Success',
            self::TYPE_SHIPMENT_UPDATE => 'Shipment Update',
            self::TYPE_LOW_STOCK => 'Low Stock Alert',
            self::TYPE_PROMOTION => 'Promotion',
        ];

        return $labels[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    public function getNotificationIconAttribute()
    {
        $icons = [
            self::TYPE_ORDER_CONFIRMATION => 'fa-shopping-cart',
            self::TYPE_WALLET_TOPUP => 'fa-wallet',
            self::TYPE_NEW_ORDER => 'fa-bell',
            self::TYPE_ORDER_STATUS_UPDATE => 'fa-truck',
            self::TYPE_PAYMENT_SUCCESS => 'fa-credit-card',
            self::TYPE_SHIPMENT_UPDATE => 'fa-shipping-fast',
            self::TYPE_LOW_STOCK => 'fa-exclamation-triangle',
            self::TYPE_PROMOTION => 'fa-tag',
        ];

        return $icons[$this->type] ?? 'fa-bell';
    }

    public static function createNotification($userId, $type, $title, $message, $data = null)
    {
        return self::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data ? json_encode($data) : null,
            'read_at' => null,
        ]);
    }

    public static function markAllAsRead($userId)
    {
        return self::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public static function cleanupOldNotifications($days = 30)
    {
        return self::where('created_at', '<', now()->subDays($days))
            ->delete();
    }

    public static function getUserNotificationsSummary($userId)
    {
        $unreadCount = self::unreadCount($userId);
        $recentNotifications = self::forUser($userId)
            ->recent(10)
            ->get();

        return [
            'unread_count' => $unreadCount,
            'recent_notifications' => $recentNotifications,
            'total_notifications' => self::forUser($userId)->count(),
        ];
    }
}
