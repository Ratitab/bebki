<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = env('ADMIN_SECRET_TOKEN');

        if (empty($secret)) {
            return response()->json(['success' => false, 'message' => 'Admin access not configured.'], 403);
        }

        $bearer = $request->bearerToken();

        if (!$bearer || !hash_equals($secret, $bearer)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        return $next($request);
    }
}
