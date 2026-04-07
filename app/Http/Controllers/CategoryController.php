<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;
use App\Traits\Resp;
use Illuminate\Http\Request;

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

    /** POST /categories/request — authenticated users submit a missing-category request */
    public function request(Request $request)
    {
        $request->validate(['title' => 'required|string|max:100']);

        $title = trim($request->title);

        $existing = \App\Models\Category::whereRaw('LOWER(title) = ?', [strtolower($title)])
            ->whereNull('deleted_at')
            ->first();

        if ($existing) {
            $message = $existing->type_id === 2
                ? 'This category is already under review.'
                : 'This category already exists.';

            return $this->apiResponseFail($message);
        }

        $category = $this->categoryService->requestCategory($title);

        return $this->apiResponseSuccess(['data' => $category]);
    }
}
