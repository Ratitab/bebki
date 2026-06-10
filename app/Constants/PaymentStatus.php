<?php

namespace App\Constants;

class PaymentStatus
{
    public const PENDING  = 'pending';
    public const PAID     = 'paid';
    public const FAILED   = 'failed';
    public const BLOCKED  = 'blocked';
    public const EXPIRED  = 'expired';
    public const REVERSED = 'reversed';

    public const ALL = [
        self::PENDING,
        self::PAID,
        self::FAILED,
        self::BLOCKED,
        self::EXPIRED,
        self::REVERSED,
    ];
}
