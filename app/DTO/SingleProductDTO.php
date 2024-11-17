<?php

namespace App\DTO;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

class SingleProductDTO
{

    public function __construct(
        public readonly ?Authenticatable $user = null,
        public readonly string $productId,
    )
    {
    }

    public static function fromRequest(Request $request, string $productId): self
    {
        $user = auth('api')->user();
        return new self(
            user: $user,
            productId: $productId,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'user_id' => $this->user?->id,
            'product_id' => $this->productId,
        ], fn($value) => !is_null($value));
    }

    public function isAuthenticated(): bool
    {
        return $this->user !== null;
    }
}
