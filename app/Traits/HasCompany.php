<?php

namespace App\Traits;

use App\Models\Companies\Address;
use App\Models\Companies\Company;
use App\Models\Companies\CompanyUser;
use App\Models\Companies\CompanyInformation;
use App\Models\Companies\CompanyInformationType;
use App\Models\Companies\CompanyType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

trait HasCompany
{
    public function company_type()
    {
        return $this->belongsTo(CompanyType::class);
    }

    public function company_information()
    {
        return $this->hasMany(CompanyInformation::class);
    }

    public function company(){
        return $this->belongsTo(Company::class);
    }

    public function company_information_type(){
        return $this->belongsTo(CompanyInformationType::class);
    }

    public function company_information_fields($company_information_type_id){
        return CompanyInformationType::where('_id',$company_information_type_id)->first();
    }

    public function company_information_values($id,$type_id)
    {
        return CompanyInformation::where('company_id',$id)->where('company_information_type_id',$type_id)->first();
    }

    public function companies(){
        return $this->hasMany(Company::class);
    }

    public function company_users(){
        return $this->hasMany(CompanyUser::class)->with(['company']);
    }

    public function addresses(){
        return $this->hasMany(Address::class);
    }

}

