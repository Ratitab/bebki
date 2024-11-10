<?php

namespace App\DTO;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

class SearchProductsDTO
{

    public function __construct(
        public readonly ?Authenticatable $user = null,
        public readonly ?string $type = null,
        public readonly ?string $createdById = null,
        public readonly ?string $category = null,
        public readonly ?string $gem = null,
        public readonly ?string $material = null,
        public readonly ?float  $minPrice = null,
        public readonly ?float  $maxPrice = null,
        public readonly ?string $city = null,
        public readonly ?string $search = null,
        public readonly ?array  $tags = null,
        public readonly ?string  $stamp = null,
        public readonly ?string  $weight = null,
        public readonly ?bool  $customizationAvailable = null,
        public readonly int     $perPage = 12,
        public readonly ?string $cursor = null,
    )
    {
    }

    public static function fromRequest(Request $request): self
    {
        $user = auth('api')->user();
        return new self(
            user: $user,
            type: $request->input('type'),
            createdById: $request->input('createdById'),
            category: $request->input('category'),
            gem: $request->input('gem'),
            material: $request->input('material'),
            minPrice: $request->float('min_price'),
            maxPrice: $request->float('max_price'),
            city: $request->input('city'),
            search: $request->input('search'),
            tags: $request->input('tags'),
            stamp: $request->input('stamp'),
            weight: $request->input('weight'),
            customizationAvailable: $request->input('customizationAvailable'),
            perPage: $request->integer('per_page', 12),
            cursor: $request->input('cursor'),
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'user_id' => $this->user?->id,
            'type' => $this->type,
            'created_by_id' => $this->createdById,
            'category' => $this->category,
            'gem' => $this->gem,
            'material' => $this->material,
            'min_price' => $this->minPrice,
            'max_price' => $this->maxPrice,
            'city' => $this->city,
            'search' => $this->search,
            'tags' => $this->tags,
            'stamp' => $this->stamp,
            'weight' => $this->weight,
            'customizationAvailable' => $this->customizationAvailable,
            'per_page' => $this->perPage,
            'cursor' => $this->cursor,
        ], fn($value) => !is_null($value));
    }

    public function isAuthenticated(): bool
    {
        return $this->user !== null;
    }
}
