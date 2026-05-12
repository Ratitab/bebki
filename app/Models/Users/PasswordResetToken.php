<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;

class PasswordResetToken extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'email';
    protected $keyType = 'string';

    protected $fillable = ['email', 'token', 'expires_at', 'created_at'];

    protected $casts = [
        'expires_at'  => 'datetime',
        'created_at'  => 'datetime',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
