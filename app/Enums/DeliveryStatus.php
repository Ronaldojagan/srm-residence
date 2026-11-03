<?php

namespace App\Enums;

enum DeliveryStatus: string
{
    case PICKED_UP = 'picked_up';
    case IN_TRANSIT = 'in_transit';
    case OUT_FOR_DELIVERY = 'out_for_delivery';
    case DELIVERED = 'delivered';
    case FAILED_ATTEMPT = 'failed_attempt';
    case RETURNED = 'returned';
    case CANCELLED = 'cancelled';
    case DELAYED = 'delayed';

    public static function getAll(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getDescription(string $status): string
    {
        return match ($status) {
            self::PICKED_UP->value => 'Picked Up',
            self::IN_TRANSIT->value => 'In Transit',
            self::OUT_FOR_DELIVERY->value => 'Out for Delivery',
            self::DELIVERED->value => 'Delivered',
            self::FAILED_ATTEMPT->value => 'Failed Attempt',
            self::RETURNED->value => 'Returned',
            self::CANCELLED->value => 'Cancelled',
            self::DELAYED->value => 'Delayed',
            default => 'Unknown',
        };
    }

    public static function getColor(string $status): string
    {
        return match ($status) {
            self::PICKED_UP->value => 'info',
            self::IN_TRANSIT->value => 'primary',
            self::OUT_FOR_DELIVERY->value => 'warning',
            self::DELIVERED->value => 'success',
            self::FAILED_ATTEMPT->value => 'danger',
            self::RETURNED->value => 'dark',
            self::CANCELLED->value => 'danger',
            self::DELAYED->value => 'warning',
            default => 'secondary',
        };
    }

    public static function isFinalStatus(string $status): bool
    {
        return in_array($status, [
            self::DELIVERED->value,
            self::RETURNED->value,
            self::CANCELLED->value,
        ]);
    }

    public static function getNextStatuses(string $currentStatus): array
    {
        return match ($currentStatus) {
            self::PICKED_UP->value => [self::IN_TRANSIT->value, self::DELAYED->value, self::CANCELLED->value],
            self::IN_TRANSIT->value => [self::OUT_FOR_DELIVERY->value, self::DELAYED->value, self::RETURNED->value],
            self::OUT_FOR_DELIVERY->value => [self::DELIVERED->value, self::FAILED_ATTEMPT->value, self::RETURNED->value],
            self::FAILED_ATTEMPT->value => [self::OUT_FOR_DELIVERY->value, self::RETURNED->value],
            self::DELAYED->value => [self::IN_TRANSIT->value, self::OUT_FOR_DELIVERY->value],
            default => [],
        };
    }
}
