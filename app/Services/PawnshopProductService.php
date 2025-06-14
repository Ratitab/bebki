<?php

namespace App\Services;

use App\Repositories\PawnshopProductRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PawnshopProductService
{
    public function __construct(
        private readonly PawnshopProductRepository $pawnshopProductRepository
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
        return DB::transaction(function () use ($company_id, $title, $material, $stamp, $weight, $gem, $size, $phoneNumber, $description, $imageUrls) {
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
