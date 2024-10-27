<?php

namespace App\Repositories;

use App\Models\Companies\CompanyUser;
use Illuminate\Support\Str;

class CompanyUserRepository
{

    public function __construct(
        private readonly CompanyUser $companyUserModel,
    ) {
    }

    public function findByUser($userId)
    {
        $companyIds = $this->companyUserModel
            ->where('user_id', $userId)
            ->pluck('company_id');

        if ($companyIds->isEmpty()) {
            return [];
        }
        $companies = $this->companyUserModel
            ->select([
                'companies.id as company_id',
                'company_users.user_id',
                'company_information.value',
                'company_information_types.name as info_type',
                'addresses.id as address_id',
                'addresses.address',
                'addresses.city',
                'addresses.state',
                'addresses.lat',
                'addresses.long',
                'addresses.email',
                'addresses.phone',
                'addresses.postal_code',
                'addresses.is_same_time',
                'addresses.start_time',
                'addresses.end_time'
            ])
            ->leftJoin('companies', 'company_users.company_id', '=', 'companies.id')
            ->leftJoin('company_information', function($join) {
                $join->on('companies.id', '=', 'company_information.company_id')
                    ->whereNull('company_information.deleted_at');
            })
            ->leftJoin('company_information_types', 'company_information.company_information_type_id', '=', 'company_information_types.id')
            ->leftJoin('addresses', function($join) {
                $join->on('companies.id', '=', 'addresses.company_id')
                    ->whereNull('addresses.deleted_at');
            })
            ->where('company_users.user_id', $userId)
            ->whereIn('company_users.company_id', $companyIds)
            ->orderBy('companies.id')
            ->cursor();

        $result = [];
        $currentCompany = null;
        $companyData = null;

        foreach ($companies as $company) {
            if ($currentCompany !== $company->company_id) {
                if ($companyData !== null) {
                    $result[] = $companyData;
                }
                $currentCompany = $company->company_id;
                $companyData = [
                    'company_id' => $company->company_id,
                    'user_id' => $company->user_id,
                    'information' => [],
                    'addresses' => []
                ];
            }
            if ($company->info_type) {
                $companyData['information'][$company->info_type] = $company->value;
            }

            if ($company->address_id && !array_key_exists($company->address_id, array_column($companyData['addresses'], 'id'))) {
                $companyData['addresses'][] = [
                    'id' => $company->address_id,
                    'address' => $company->address,
                    'city' => $company->city,
                    'state' => $company->state,
                    'lat' => $company->lat,
                    'long' => $company->long,
                    'email' => $company->email,
                    'phone' => $company->phone,
                    'postal_code' => $company->postal_code,
                    'is_same_time' => $company->is_same_time,
                ];
            }
        }
        if ($companyData !== null) {
            $result[] = $companyData;
        }

        return $result;
    }
    public function create($company_id, $user_id)
    {
        $company_user = new $this->companyUserModel;
        $company_user->company_id = $company_id;
        $company_user->user_id = $user_id;
        $company_user->save();
        return $company_user;

    }

    public function delete($id)
    {
        return $this->companyUserModel->where('id', $id)->delete();
    }

}
