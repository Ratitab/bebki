<?php

namespace App\Models\Products;

use App\Models\Users\UserFavourite;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class Limit extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'mongodb';
    protected $collection = 'limits';

}
