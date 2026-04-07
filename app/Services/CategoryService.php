<?php

namespace App\Services;

use App\Repositories\CategoryRepository;

class CategoryService
{
    public function __construct(private readonly CategoryRepository $categoryRepository)
    {
    }

    public function requestCategory(string $title)
    {
        return $this->categoryRepository->createRequested(trim($title));
    }

    public function getRequested()
    {
        return $this->categoryRepository->findRequested();
    }

    public function approveCategory(int $id, int $typeId, int $parentId = 0): bool
    {
        return $this->categoryRepository->approveRequested($id, $typeId, $parentId);
    }

    public function getGrouped(): array
    {
        $all = $this->categoryRepository->getAll();

        return [
            'product_types' => $this->buildTree($all->where('type_id', 1)->values()),
            'materials'     => $this->buildTree($all->where('type_id', 0)->values()),
        ];
    }

    /**
     * Build a tree from a flat collection.
     * Top-level items have parent_id = 0; children have parent_id = their parent's id.
     */
    private function buildTree(\Illuminate\Support\Collection $items): array
    {
        $childrenMap = [];
        foreach ($items as $item) {
            if ((int) $item->parent_id !== 0) {
                $childrenMap[(int) $item->parent_id][] = [
                    'id'    => $item->id,
                    'title' => $item->title,
                ];
            }
        }

        $tree = [];
        foreach ($items as $item) {
            if ((int) $item->parent_id !== 0) {
                continue;
            }
            $node = [
                'id'    => $item->id,
                'title' => $item->title,
            ];
            $subs = $childrenMap[(int) $item->id] ?? [];
            if (!empty($subs)) {
                $node['subcategories'] = $subs;
            }
            $tree[] = $node;
        }

        return $tree;
    }
}
