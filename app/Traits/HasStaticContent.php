<?php

namespace App\Traits;

use App\Models\Categories\CategoryType;
use App\Models\Categories\CategoryNode;
use App\Models\Categories\CategoryNodeType;
use App\Models\Categories\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

trait HasStaticContent
{
    public function contentType(){
        return $this->hasOne('\App\Models\StaticContents\StaticContentType','id','type_id');
    }
}
