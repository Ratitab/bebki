<?php

namespace App\Constants;

class ShopStatus
{
    public const USER = 'user';
    public const PENDING = 'pending';
    public const VERIFIED = 'verified';
    public const REJECTED = 'rejected';

    public const ALL = [
        self::USER,
        self::PENDING,
        self::VERIFIED,
        self::REJECTED,
    ];
}
