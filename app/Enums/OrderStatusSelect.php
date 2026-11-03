<?php

namespace App\Enums;

enum OrderStatusSelect: string
{
    case PENDING    = 'pending';
    case PROCESSING = 'processing';
    case SHIPPED    = 'shipped';
    case DELIVERED  = 'delivered';
    case CANCELLED  = 'cancelled';
    case RETURNED   = 'returned';
    case REPLACED   = 'replaced';

    public static function toSelect(): array
    {
        return [
            self::PENDING->value    => 'Pending',
            self::PROCESSING->value => 'Processing',
            self::SHIPPED->value    => 'Shipped',
            self::DELIVERED->value  => 'Delivered',
            self::CANCELLED->value  => 'Cancelled',
            self::RETURNED->value   => 'Returned',
            self::REPLACED->value   => 'Replaced',
        ];
    }
}
