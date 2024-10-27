<?php

namespace App\Repositories;

use App\Models\Countries\City;
use Illuminate\Support\Str;

class CityRepository
{

    public function __construct(
        private readonly City $cityModel,
    ) {
    }

    public function findByCountryId($countryId)
    {
        return $this->cityModel->where('is_active', 1)->where('country_id',$countryId)->get();
    }

    public function delete($id)
    {
        return $this->cityModel->where('id', $id)->delete();
    }

}
