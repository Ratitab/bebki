<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'order_number',
        'status',
        'items',
        'shipping',
        'total',
        'currency',
        'notes',
    ];

    protected $casts = [
        'items'    => 'array',
        'shipping' => 'array',
    ];
}
