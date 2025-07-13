<?php

namespace App\Services;

use App\Repositories\PawnshopProductRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PawnshopProductService
{
    public function __construct(
        private readonly PawnshopProductRepository $pawnshopProductRepository,
        private readonly CompanyInformationService $companyInformationService
    ) {}

    public function findMany($company_id, $search)
    {
        return $this->pawnshopProductRepository->findManyByPawnshopId($company_id, $search);
    }

    public function findOne($id)
    {
        return $this->pawnshopProductRepository->findOneById($id);
    }

    public function create($company_id, $title, $material, $stamp, $weight, $gem, $size, $phoneNumber, $description, $imageUrls)
    {
        $product = DB::transaction(function () use ($company_id, $title, $material, $stamp, $weight, $gem, $size, $phoneNumber, $description, $imageUrls) {
            return $this->pawnshopProductRepository->create(
                $company_id,
                $title,
                $material,
                $stamp,
                $weight,
                $gem,
                $size,
                $phoneNumber,
                $description,
                $imageUrls
            );
        });
        if($product) {
            // Email content
            $companyEmail = $this->companyInformationService->findOneByCompanyAndTypeId($company_id,2)['value'];
            if($companyEmail) {
                $emailContent = "თქვენ მიიღეთ ახალი შეთავაზება. გთხოვთ გადახვიდეთ პირად კაბინეტში და ნახოთ შეტყობინება. \n https://gegold.ge/dashboard-company/".$company_id;
                try {
                    Mail::raw($emailContent, function ($message) use ($companyEmail,$title) {
                        $message->to($companyEmail)
                        ->subject('GEGOLD - ახალი პროდუქტი'.$title);
                    });
                } catch (\Throwable $e) {
                    \Log::error('Failed to send email: ' . $e->getMessage());
                    // Don't throw - let product creation continue
                }
            }
            return $product;
        }
        return false;
    }

    public function update($id, $company_id, $title, $material, $stamp, $weight, $gem, $size, $phoneNumber, $description, $imageUrls)
    {
        return DB::transaction(function () use ($id, $company_id, $title, $material, $stamp, $weight, $gem, $size, $phoneNumber, $description, $imageUrls) {
            return $this->pawnshopProductRepository->update(
                $id,
                $company_id,
                $title,
                $material,
                $stamp,
                $weight,
                $gem,
                $size,
                $phoneNumber,
                $description,
                $imageUrls
            );
        });
    }

    public function changeStatus($id,$status)
    {
        return $this->pawnshopProductRepository->changeStatus($id,$status);
    }

    public function delete($id)
    {
        return $this->pawnshopProductRepository->delete($id);
    }
}
