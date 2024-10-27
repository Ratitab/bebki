<?php

namespace App\Repositories;

use App\Models\Countries\Country;
use Illuminate\Support\Str;

class CountryRepository
{

    public function __construct(
        private readonly Country $countryModel,
    ) {
    }

    public function get()
    {
        return $this->countryModel->where('is_active', 1)->get();
    }

    public function delete($id)
    {
        return $this->countryModel->where('id', $id)->delete();
    }

}
