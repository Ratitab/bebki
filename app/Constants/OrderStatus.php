<?php

namespace App\Constants;

class OrderStatus
{
    public const PENDING    = 'pending';
    public const PROCESSING = 'processing';
    public const SHIPPED    = 'shipped';
    public const DELIVERED  = 'delivered';
    public const CANCELLED  = 'cancelled';

    public const ALL = [
        self::PENDING,
        self::PROCESSING,
        self::SHIPPED,
        self::DELIVERED,
        self::CANCELLED,
    ];
}
