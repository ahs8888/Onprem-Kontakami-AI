<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Data\Setting;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AccessTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Authorization');

        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return response()->json(['message' => 'Unauthorized: Missing bearer token'], 401);
        }

        $providedToken = trim(str_replace('Bearer', '', $header));

        $storedToken = Setting::where("key", "access_token")->value("value");

        if (!$storedToken || $providedToken !== $storedToken) {
            return response()->json(['message' => 'Unauthorized: Invalid token'], 401);
        }

        return $next($request);
    }
}
