<?php

namespace App\Rules;

use App\Models\Products\Product;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidUserOrCompanyProduct implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Get requested values
        $requestedCreatedById = request()->input('created_by.id');
        $requestedCreatedByType = request()->input('created_by.type');

        // Get product
        $product = Product::find($value);

        if (!$product) {
            $fail('Product not found');
            return;
        }

        // Check if created_by matches
        if ($product->created_by['id'] !== $requestedCreatedById ||
            $product->created_by['type'] !== $requestedCreatedByType) {
            $fail('Permission Denied');
        }
    }
}
