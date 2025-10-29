<?php

namespace App\Http\Middleware;

use Closure;
use App\Enum\UserStatus;
use App\Models\RequestLog;
use App\Models\Account\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthenticateMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$token = $request->bearerToken()) {
            return $this->block();
        }
        $user = User::query()
            ->where('status', UserStatus::Active)
            ->where('code', $token)
            ->first();

        RequestLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'token' => $token,
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'params' => $request->all()
        ]);

        if (!$user) {
            return $this->block();
        }

        $request->merge(['SESSION_USER_ID' => $user->id]);
        return $next($request);
    }

    private function block()
    {
        return response()->json([
            'status' => 'unathorized'
        ], Response::HTTP_UNAUTHORIZED);
    }
}
