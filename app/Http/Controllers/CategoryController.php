<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;
use App\Traits\Resp;

class CategoryController extends Controller
{
    use Resp;

    public function __construct(private readonly CategoryService $categoryService)
    {
    }

    /** GET /categories — returns { product_types: [], materials: [] } */
    public function index()
    {
        return $this->apiResponseSuccess(['data' => $this->categoryService->getGrouped()]);
    }
}
