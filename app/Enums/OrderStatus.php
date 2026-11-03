<?php

namespace App\Enums;

class OrderStatus extends Enum
{
    public const PENDING = 'Pending';
    public const PROCESSING = 'Processing';
    public const SHIPPED = 'Shipped';
    public const COMPLETED = 'Completed';
    public const CANCELLED = 'Cancelled';
    public const REFUNDED = 'Refunded';
    public const DELIVERED = 'Delivered';

    public static function getAll(): array
    {
        return [
            self::PENDING,
            self::PROCESSING,
            self::SHIPPED,
            self::COMPLETED,
            self::DELIVERED,
            self::CANCELLED,
            self::REFUNDED,
        ];
    }

    public static function get(): array
    {
        return [
            self::PENDING,
            self::PROCESSING,
            self::SHIPPED,
            self::COMPLETED,
            self::DELIVERED,
            self::CANCELLED,
            self::REFUNDED,
        ];
    }

    public static function getDescription($value): string
    {
        return match ($value) {
            self::PENDING => 'Pending',
            self::PROCESSING => 'Processing',
            self::SHIPPED => 'Shipped',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
            self::REFUNDED => 'Refunded',
            self::DELIVERED => 'Delivered',
            default => ucfirst($value),
        };
    }

    public static function canCancel($status): bool
    {
        return in_array($status, [self::PENDING]);
    }
}
