<?php

namespace App\Models\Companies;

use App\Models\Products\Product;
use App\Models\Calendars\Calendar;
use App\Models\Users\SwitchProfile;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;


class Company extends Model
{
    use HasFactory, SoftDeletes, HasCompany, Notifiable;

    protected $keyType = 'string';
    public $incrementing = false;
//    protected $appends = ['information'];


//    public function getInformationAttribute()
//    {
//        $companyInformationCollection = [];
//
//        $companyInformation = CompanyInformation::leftJoin('company_information_types', 'company_information.company_information_type_id', '=', 'company_information_types.id')
//            ->where('company_information.company_id', $this->id)
//            ->whereNull('company_information.deleted_at')
//            ->select('company_information.value', 'company_information_types.name')
//            ->get();
//
//        foreach ($companyInformation as $info) {
//            $companyInformationCollection[$info->name] = $info->value;
//        }
//
//        return $companyInformationCollection;
//    }
}
