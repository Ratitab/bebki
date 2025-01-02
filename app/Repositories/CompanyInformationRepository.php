<?php

namespace App\Repositories;

use App\Models\Companies\CompanyInformation;
use Illuminate\Support\Facades\DB;

class CompanyInformationRepository
{

    public function __construct(
        private readonly CompanyInformation               $companyInformationModel,
        private readonly CompanyInformationTypeRepository $companyInformationTypeRepository,
    )
    {
    }

    public function createCompanyInformation($companyId, $companyInformation)
    {
        $informationTypes = $this->companyInformationTypeRepository->getAllInformationTypes();
        $bulkInsertData = [];
        foreach ($informationTypes as $typeName => $typeId) {
            if (isset($companyInformation[$typeName])) {
                $bulkInsertData[] = [
                    'company_id' => $companyId,
                    'company_information_type_id' => $typeId,
                    'value' => $companyInformation[$typeName],
                    'verified_at' => null,
                ];
            }
        }
        $this->companyInformationModel->insert($bulkInsertData);
        return true;
    }

    public function updateCompanyInformation($companyId, $companyInformation)
    {
        $informationTypes = $this->companyInformationTypeRepository->getAllInformationTypes();
        $bulkUpdateData = [];

        foreach ($informationTypes as $typeName => $typeId) {
            if (isset($companyInformation[$typeName]) || $companyInformation[$typeName] == '') {
                $bulkUpdateData[] = [
                    'company_id' => $companyId,
                    'company_information_type_id' => $typeId,
                    'value' => $companyInformation[$typeName],
                    'verified_at' => null,
                ];
            }
        }
        \DB::transaction(function () use ($companyId, $bulkUpdateData) {
            $this->companyInformationModel
                ->where('company_id', $companyId)
                ->whereIn('company_information_type_id', array_column($bulkUpdateData, 'company_information_type_id'))
                ->delete();

            $this->companyInformationModel->insert($bulkUpdateData);
        });
        return true;
    }
}
