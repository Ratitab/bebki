<?php

namespace App\Models\Users;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasUpload;
use App\Traits\HasUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;
    use HasUser, HasUpload;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected $keyType = 'string';
    public $incrementing = false;
//    protected $appends = ['information'];

//    public function getInformationAttribute()
//    {
//        $userInformationCollection = [];
//        $userInformation = UserInformation::leftJoin('user_information_types', 'user_information.user_information_type_id', '=', 'user_information_types.id')
//            ->where('user_information.user_id', $this->id)
//            ->whereNull('user_information.deleted_at')
//            ->select('user_information.value', 'user_information_types.name')
//            ->get();
//        foreach ($userInformation as $info) {
//            $userInformationCollection[$info->name] = $info->value;
//        }
//
//        return $userInformationCollection;
//    }
}
