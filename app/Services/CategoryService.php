<?php

namespace App\Services;

use App\Repositories\CategoryRepository;

class CategoryService
{
    public function __construct(private readonly CategoryRepository $categoryRepository)
    {
    }

    public function getGrouped(): array
    {
        $all = $this->categoryRepository->getAll();

        return [
            'product_types' => $all->where('type_id', 1)->values(),
            'materials'     => $all->where('type_id', 0)->values(),
        ];
    }
}
