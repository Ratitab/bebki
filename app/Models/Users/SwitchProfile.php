<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SwitchProfile extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['active_profile_id'];
}
