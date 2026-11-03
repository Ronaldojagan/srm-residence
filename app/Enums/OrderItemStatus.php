<?php

namespace App\Enums;

enum OrderItemStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
    case RETURN_REQUESTED = 'return_requested';
    case RETURNED = 'returned';
    case REPLACED = 'replaced';
    case REFUNDED = 'refunded';
    case REPLACE_REQUESTED = 'replace_requested';
    case RETURN_APPROVED = 'return_approved';
    case REPLACE_APPROVED = 'replace_approved';
    case SHOP_APPROVED = 'shop_approved';
    case SHOP_REJECTED = 'shop_rejected';

    public static function getAll(): array
    {
        return [
            self::PENDING->value,
            self::CONFIRMED->value,
            self::PROCESSING->value,
            self::SHIPPED->value,
            self::DELIVERED->value,
            self::CANCELLED->value,
            self::RETURN_REQUESTED->value,
            self::REPLACE_REQUESTED->value,
            self::RETURNED->value,
            self::REPLACED->value,
            self::REFUNDED->value,
            self::RETURN_APPROVED->value,
            self::REPLACE_APPROVED->value,
            self::SHOP_APPROVED->value,
            self::SHOP_REJECTED->value,
        ];
    }

    public static function getDescription(string $status): string
    {
        return match ($status) {
            self::PENDING->value => 'Pending',
            self::CONFIRMED->value => 'Confirmed',
            self::PROCESSING->value => 'Processing',
            self::SHIPPED->value => 'Shipped',
            self::DELIVERED->value => 'Delivered',
            self::CANCELLED->value => 'Cancelled',
            self::RETURN_REQUESTED->value => 'Return Requested',
            self::RETURN_APPROVED->value => 'Return Approved',
            self::RETURNED->value => 'Returned',
            self::REFUNDED->value => 'Refunded',
            self::REPLACED->value => 'Replaced',
            self::REPLACE_REQUESTED->value => 'Replace Requested',
            self::REPLACE_APPROVED->value => 'Replace Approved',
            self::SHOP_APPROVED->value => 'Shop Approved',
            self::SHOP_REJECTED->value => 'Shop Rejected',
            default => 'Unknown',
        };
    }

    public static function getColor(string $status): string
    {
        return match ($status) {
            self::PENDING->value => 'warning',
            self::CONFIRMED->value => 'info',
            self::PROCESSING->value => 'primary',
            self::SHIPPED->value => 'secondary',
            self::DELIVERED->value => 'success',
            self::CANCELLED->value => 'danger',
            self::RETURN_REQUESTED->value => 'warning',
            self::RETURNED->value => 'dark',
            self::REFUNDED->value => 'info',
            default => 'secondary',
        };
    }

    public static function canCancel(string $status): bool
    {
        return in_array($status, [
            self::PENDING->value,
            self::CONFIRMED->value,
            self::PROCESSING->value,
        ]);
    }

    public static function canReturn(string $status): bool
    {
        return $status === self::DELIVERED->value;
    }

    public static function canReplace(string $status): bool
    {
        return in_array($status, [
            self::DELIVERED->value,
        ]);
    }

    public static function isActive(string $status): bool
    {
        return !in_array($status, [
            self::CANCELLED->value,
            self::RETURNED->value,
            self::REFUNDED->value,
        ]);
    }

    public static function getNextStatus(string $currentStatus): array
    {
        return match ($currentStatus) {
            self::PENDING->value => [self::CONFIRMED->value, self::CANCELLED->value],
            self::CONFIRMED->value => [self::PROCESSING->value, self::CANCELLED->value],
            self::PROCESSING->value => [self::SHIPPED->value, self::CANCELLED->value],
            self::SHIPPED->value => [self::DELIVERED->value],
            self::DELIVERED->value => [self::RETURN_REQUESTED->value],
            self::RETURN_REQUESTED->value => [self::RETURNED->value, self::REFUNDED->value],
            self::RETURNED->value => [self::REFUNDED->value],
            default => [],
        };
    }

    public static function getRefundableStatuses(): array
    {
        return [
            self::CANCELLED->value,
            self::RETURNED->value,
            self::REFUNDED->value,
        ];
    }

    public static function canShip(string $status): bool
    {
        return in_array($status, [
            self::CONFIRMED->value,
            self::PROCESSING->value,
        ]);
    }
}
