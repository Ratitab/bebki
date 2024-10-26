<?php

namespace App\Models\Companies;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class CompanyType extends Model
{
    use HasFactory, SoftDeletes;
}
