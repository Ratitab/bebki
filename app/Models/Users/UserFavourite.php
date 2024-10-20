<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserFavourite extends Model
{
    use HasFactory, SoftDeletes;

//    protected $keyType = 'string';
//    public $incrementing = false;
}
