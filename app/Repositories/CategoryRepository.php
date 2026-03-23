<?php

namespace App\Repositories;

use App\Models\Category;

class CategoryRepository
{
    public function __construct(private readonly Category $categoryModel)
    {
    }

    /** type_id=1 → product types, type_id=0 → materials */
    public function findByType(int $typeId)
    {
        return $this->categoryModel
            ->whereNull('deleted_at')
            ->where('type_id', $typeId)
            ->orderBy('order_id')
            ->orderBy('title')
            ->get(['id', 'title', 'type_id', 'icon']);
    }

    public function getAll()
    {
        return $this->categoryModel
            ->whereNull('deleted_at')
            ->orderBy('type_id')
            ->orderBy('order_id')
            ->orderBy('title')
            ->get(['id', 'title', 'type_id', 'icon']);
    }
}
