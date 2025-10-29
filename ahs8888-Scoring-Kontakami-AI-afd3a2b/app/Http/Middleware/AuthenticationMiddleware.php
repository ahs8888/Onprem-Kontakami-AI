<?php

namespace App\Http\Middleware;

use App\Models\Account\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!$user = user()){
            if(!$admin = admin()){
                return to_route('auth.login.index');
            }
            if($admin && !request()->routeIs('setup.recording-analysis.*')){
                return to_route('auth.login.index');
            }
        }

        return $next($request);
    }

}
