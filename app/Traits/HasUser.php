<?php

namespace App\Traits;



use App\Models\Users\AccountConnection;
use App\Models\Users\User;
use App\Models\Users\UserInformation;
use App\Models\Users\UserInformationType;

use App\Models\Users\UserStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

trait HasUser{

    public function AuthAcesToken()
    {
        return $this->hasMany(\App\Models\OauthAccessToken::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function account_connection()
    {
        return $this->hasMany(AccountConnection::class);
    }

    public function user_status()
    {
        return $this->belongsTo(UserStatus::class);
    }

    public function uploaded_by(){
        return $this->belongsTo(User::class);
    }

    public function user_information()
    {
        return $this->hasMany(UserInformation::class);
    }

    public function userInformationTypes(){
        return $this->belongsTo(UserInformationType::class, 'user_information_types', 'id');
    }

    public function user_information_fields($user_information_type_id){
        return UserInformationType::where('id',$user_information_type_id)->first();
    }

    public function user_information_values($id,$type_id)
    {
        return UserInformation::where('user_id',$id)->where('user_information_type_id',$type_id)->first();
    }


}
