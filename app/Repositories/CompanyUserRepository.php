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

    public function findManyByUser($userId)
    {

        $companies = $this->companyUserModel
            ->select([
                'companies.id as company_id',
                'companies.identification_number',
                'companies.company_type_id',
                'company_users.user_id',
                'company_information.value',
                'company_information_types.name as info_type'
            ])
            ->leftJoin('companies', 'company_users.company_id', '=', 'companies.id')
            ->leftJoin('company_information', function ($join) {
                $join->on('companies.id', '=', 'company_information.company_id')
                    ->whereNull('company_information.deleted_at');
            })
            ->leftJoin('company_information_types', 'company_information.company_information_type_id', '=', 'company_information_types.id')
            ->where('company_users.user_id', $userId)
            ->where('user_id', $userId)
            ->orderBy('companies.id')
            ->get();

        $result = [];
        $currentCompany = null;
        $companyData = null;

        foreach ($companies as $company) {
            // Check if we're on a new company, add the last one to results if so
            if ($currentCompany !== $company->company_id) {
                if ($companyData !== null) {
                    $result[] = $companyData;
                }
                $currentCompany = $company->company_id;
                $companyData = [
                    'company_id' => $company->company_id,
                    'user_id' => $company->user_id,
                    'identification_number' => $company->identification_number,
                    'company_type_id' => $company->company_type_id,
                    'information' => [],
                ];
                $addressIds = [];  // Hash map for unique addresses in the current company
            }

            if ($company->info_type) {
                $companyData['information'][$company->info_type] = $company->value;
            }
        }

        if ($companyData !== null) {
            $result[] = $companyData;
        }

        return $result;
    }

    public function findOneByUser($userId,$companyId)
    {

        $company = $this->companyUserModel
            ->select([
                'companies.id as company_id',
                'companies.identification_number',
                'companies.company_type_id',
                'company_users.user_id',
                'company_information.value',
                'company_information_types.name as info_type',
                'addresses.id as address_id',
                'addresses.address',
                'cities.name as city',
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
            ->leftJoin('company_information', function ($join) {
                $join->on('companies.id', '=', 'company_information.company_id')
                    ->whereNull('company_information.deleted_at');
            })
            ->leftJoin('company_information_types', 'company_information.company_information_type_id', '=', 'company_information_types.id')
            ->leftJoin('addresses', function ($join) {
                $join->on('companies.id', '=', 'addresses.company_id')
                    ->whereNull('addresses.deleted_at');
            })
            ->leftJoin('cities', function ($join) {
                $join->on('addresses.city', '=', \DB::raw('CAST(cities.id AS VARCHAR)'));
            })
            ->where('company_users.user_id', $userId)
            ->where('company_users.company_id', $companyId)
            ->first();

        if (!$company) {
            return [];
        }

        $result = [
            'company_id' => $company->company_id,
            'user_id' => $company->user_id,
            'identification_number' => $company->identification_number,
            'company_type_id' => $company->company_type_id,
            'information' => [],
            'addresses' => []
        ];

        if ($company->info_type) {
            $result['information'][$company->info_type] = $company->value;
        }

        $addressIds = [];  // Track unique addresses for this company
        if ($company->address_id && !in_array($company->address_id, $addressIds, true)) {
            $addressIds[] = $company->address_id;
            $result['addresses'][] = [
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
                'start_time' => $company->start_time,
                'end_time' => $company->end_time
            ];
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
