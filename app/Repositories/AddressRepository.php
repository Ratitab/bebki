<?php

namespace App\Repositories;

use App\Models\Companies\Address;
use Illuminate\Support\Str;

class AddressRepository
{

    public function __construct(
        private readonly Address $addressModel,
    ) {
    }
    public function createOrUpdate($company_id, $address,$city,$state,$lat,$long,$email,$phone,$postal_code,$is_same_time,$start_time,$end_time,$address_id = null)
    {

        $addresses = $this->addressModel->find($address_id) ?? new $this->addressModel;
        $addresses->id = $addresses->id ?? Str::uuid();
        return $this->addressData($addresses,$company_id, $address,$city,$state,$lat,$long,$email,$phone,$postal_code,$is_same_time,$start_time,$end_time);
    }

    public function addressData($addresses,$company_id, $address,$city,$state,$lat,$long,$email,$phone,$postal_code,$is_same_time,$start_time,$end_time)
    {
        $addresses->company_id = $company_id;
        $addresses->address = $address;
        $addresses->city = $city ?? null;
        $addresses->state = $state ?? null;
        $addresses->lat = $lat ?? null;
        $addresses->long = $long ?? null;
        $addresses->email = $email ?? null;
        $addresses->phone = $phone ?? null;
        $addresses->postal_code = $postal_code ?? null;
        $addresses->is_same_time = $is_same_time ?? true;
        $addresses->start_time = $start_time ?? null;
        $addresses->end_time = $end_time ?? null;
        return $addresses;
    }

    public function delete($id)
    {
        return $this->addressModel->where('id', $id)->delete();
    }

}
