<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserInformation extends Model
{
    use HasFactory, SoftDeletes;

    public function user()
    {
        return $this->belongsTo(\App\Models\Users\User::class);
    }

    public function user_information_type()
    {
        return $this->belongsTo(\App\Models\Users\UserInformationType::class);
    }
}
