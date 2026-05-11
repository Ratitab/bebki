<?php

namespace App\Constants;

class OrderStatus
{
    public const PENDING    = 'pending';
    public const READY_TO_SHIP = 'ready_to_ship';
    public const SHIPPED    = 'shipped';
    public const DELIVERED  = 'delivered';
    public const CANCELLED  = 'cancelled';

    public const ALL = [
        self::PENDING,
        self::READY_TO_SHIP,
        self::SHIPPED,
        self::DELIVERED,
        self::CANCELLED,
    ];
}
