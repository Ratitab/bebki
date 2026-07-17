<?php

namespace App\Repositories;

use App\Constants\ShopStatus;
use App\Models\Companies\Company;
use App\Models\Companies\CompanyInformation;
use App\Models\Companies\CompanyUser;
use App\Models\Users\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CompanyRepository
{

    public function __construct(
        private readonly Company $companyModel,
        private readonly CompanyInformation $companyInformationModel,
        private readonly AddressRepository $addressRepository,
        private readonly UserInformationTypeRepository $userInformationTypeRepository,
    ) {
    }

    public function findAll($companyTypeId = null)
    {
        $query = $this->companyModel->newQuery()
            ->select(['id','company_type_id'])
            ->when($companyTypeId !== null, fn($q) => $q->where('company_type_id', $companyTypeId));

        $companies = $query->orderBy('id')->cursorPaginate(12);

        if ($companies->count()) {
            $companyIds = array_column($companies->items(), 'id');

            $companyInfo = CompanyInformation::select([
                'company_information.company_id',
                'company_information.value',
                'company_information_types.name'
            ])
                ->join('company_information_types', 'company_information.company_information_type_id', '=', 'company_information_types.id')
                ->whereIn('company_information.company_id', $companyIds)
                ->whereNull('company_information.deleted_at')
                ->whereNotIn('company_information_types.name', ['phone_numbers', 'email'])
                ->get();

            // Pre-build information arrays for all companies
            $infoArray = [];
            foreach($companyInfo as $info) {
                $infoArray[$info->company_id][$info->name] = $info->value;
            }

            // Convert to array with pre-built information
            $companiesArray = array_map(function($company) use ($infoArray) {
                $data = $company->toArray();
                $data['information'] = $infoArray[$company->id] ?? [];
                return $data;
            }, $companies->items());

            $companies->setCollection(collect($companiesArray));
        }

        return $companies;
    }


    public function findManyById($companyIds)
    {
        $companies = $this->companyModel->whereIn('id', $companyIds)->get();

        // Step 2: Fetch all related company information in a single query
        $allCompanyInformation = $this->companyInformationModel->leftJoin('company_information_types', 'company_information.company_information_type_id', '=', 'company_information_types.id')
            ->whereIn('company_information.company_id', $companyIds)
            ->whereNull('company_information.deleted_at')
            ->select('company_information.company_id', 'company_information.value', 'company_information_types.name')
            ->get();
        $companyInformationGrouped = $allCompanyInformation->groupBy('company_id');
        $companies->map(function ($company) use ($companyInformationGrouped) {
            $informationCollection = [];
            if (isset($companyInformationGrouped[$company->id])) {
                foreach ($companyInformationGrouped[$company->id] as $info) {
                    $informationCollection[$info->name] = $info->value;
                }
            }
            $company->setAttribute('information',$informationCollection);  // Dynamically set the information attribute
            return $company;
        });
        return $companies;
    }

    public function findOneById(string $companyId)
    {
        // Step 1: Fetch the company
        $company = $this->companyModel->find($companyId);

        if (!$company) {
            return null;
        }

        // Step 2: Fetch company information in a single query
        $companyInformation = $this->companyInformationModel
            ->leftJoin(
                'company_information_types',
                'company_information.company_information_type_id',
                '=',
                'company_information_types.id'
            )
            ->where('company_information.company_id', $companyId)
            ->whereNull('company_information.deleted_at')
            ->select(
                'company_information.value',
                'company_information_types.name'
            )
            ->get();
        $companyAddress = $this->addressRepository->findAddressByCompanyId($companyId);
        // Step 3: Transform information into associative array
        $informationCollection = [];
        foreach ($companyInformation as $info) {
            $informationCollection[$info->name] = $info->value;
        }
        // Step 3b: Fallback — if no company phone, use the linked user's phone
        if (empty($informationCollection['phone_numbers'])) {
            $userPhone = DB::table('company_users')
                ->join('user_information', 'company_users.user_id', '=', 'user_information.user_id')
                ->join('user_information_types', 'user_information.user_information_type_id', '=', 'user_information_types.id')
                ->where('company_users.company_id', $companyId)
                ->where('user_information_types.name', 'phone')
                ->whereNull('user_information.deleted_at')
                ->value('user_information.value');
            if ($userPhone) {
                $informationCollection['phone_numbers'] = $userPhone;
            }
        }

        // Step 4: Fetch shop_status from user_information via company_users
        $shopStatus = DB::table('company_users')
            ->join('user_information', function ($join) {
                $join->on('company_users.user_id', '=', 'user_information.user_id')
                    ->where('user_information.user_information_type_id', 6)
                    ->whereNull('user_information.deleted_at');
            })
            ->where('company_users.company_id', $companyId)
            ->value('user_information.value');

        // Step 5: Set the attributes
        $company->setAttribute('information', $informationCollection);
        $company->setAttribute('addresses', $companyAddress);
        $company->setAttribute('shop_status', $shopStatus ?? 'pending');

        return $company;
    }
    public function create($company_type_id)
    {
        $company = new $this->companyModel;
        $company->id = Str::uuid();
        $company->company_sku = str_pad(random_int(0, 9999999), 7, '0', STR_PAD_LEFT);
        $company->company_type_id = $company_type_id;
        $company->is_active = 1;
        $company->save();
        return $company;
    }

    public function findOutOfOrderIds(): array
    {
        return $this->companyModel
            ->where('is_out_of_order', 1)
            ->whereNull('deleted_at')
            ->pluck('id')
            ->toArray();
    }

    /**
     * Company IDs whose owning user's shop is not (or no longer) verified —
     * e.g. rejected after products were already added while verified, or still pending.
     * Used to keep such shops' listings out of public browse/search/homepage feeds.
     */
    public function findNonVerifiedCompanyIds(): array
    {
        $typeId = $this->userInformationTypeRepository->getAllInformationTypes()['shop_status'] ?? null;
        if (!$typeId) {
            return [];
        }

        return DB::table('company_users')
            ->join('user_information', function ($join) use ($typeId) {
                $join->on('company_users.user_id', '=', 'user_information.user_id')
                    ->where('user_information.user_information_type_id', $typeId)
                    ->whereNull('user_information.deleted_at');
            })
            ->where('user_information.value', '!=', ShopStatus::VERIFIED)
            ->pluck('company_users.company_id')
            ->unique()
            ->values()
            ->toArray();
    }

    public function setOutOfOrder(string $companyId, bool $value): void
    {
        $this->companyModel->where('id', $companyId)->update(['is_out_of_order' => $value ? 1 : 0]);
    }

    public function delete($id)
    {
        return $this->companyModel->where('id', $id)->delete();
    }

}
