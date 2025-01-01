<?php

namespace App\Http\Middleware;

use App\Models\Products\Product;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackProductViews
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $productId = $request->route('productId'); // Get the product ID from the route.
        if ($productId) {
            Product::where('_id', $productId)->increment('views_count');
        }
        return $next($request);
    }
}
