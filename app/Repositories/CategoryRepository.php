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
            ->get(['id', 'parent_id', 'title', 'type_id', 'icon']);
    }

    public function getAll()
    {
        return $this->categoryModel
            ->whereNull('deleted_at')
            ->orderBy('type_id')
            ->orderBy('order_id')
            ->orderBy('title')
            ->get(['id', 'parent_id', 'title', 'type_id', 'icon']);
    }

    /** type_id = 2 → user-requested categories awaiting admin review */
    public function findRequested()
    {
        return $this->categoryModel
            ->whereNull('deleted_at')
            ->where('type_id', 2)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'title', 'type_id', 'created_at']);
    }

    public function createRequested(string $title): \App\Models\Category
    {
        return $this->categoryModel->create([
            'parent_id' => 0,
            'title'     => $title,
            'type_id'   => 2,
            'order_id'  => 1,
        ]);
    }

    public function approveRequested(int $id, int $typeId, int $parentId = 0): bool
    {
        return (bool) $this->categoryModel
            ->where('id', $id)
            ->where('type_id', 2)
            ->update(['type_id' => $typeId, 'parent_id' => $parentId]);
    }
}
