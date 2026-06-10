<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderArtisanStatus extends Model
{
    protected $fillable = ['order_id', 'company_id', 'status'];
}
