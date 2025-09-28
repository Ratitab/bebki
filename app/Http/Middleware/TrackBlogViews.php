<?php

namespace App\Http\Middleware;

use App\Models\Blog\Blog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackBlogViews
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $slug = $request->route('slug'); // Get the product ID from the route.
        if ($slug) {
            Blog::where('slug', $slug)->increment('views_count');
        }
        return $next($request);
    }
}
