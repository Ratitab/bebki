<?php

namespace App\Models\Signatures;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class ImageSignature extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'mongodb';
    protected $collection = 'image_signatures';

    protected $casts = [
        'expire_datetime' => 'datetime',
    ];

}
