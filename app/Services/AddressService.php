<?php

namespace App\Services;

use App\Repositories\AddressRepository;
use App\Repositories\CompanyRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AddressService
{
    public function __construct(private readonly AddressRepository $addressRepository)
    {
    }

    public function createOrUpdate($company_id, $address,$city,$state,$lat,$long,$email,$phone,$postal_code,$is_same_time,$start_time,$end_time,$address_id = null)
    {
        return $this->addressRepository->createOrUpdate($company_id, $address,$city,$state,$lat,$long,$email,$phone,$postal_code,$is_same_time,$start_time,$end_time,$address_id);
    }
    public function delete($address_id)
    {
        return $this->addressRepository->delete($address_id);
    }

}
