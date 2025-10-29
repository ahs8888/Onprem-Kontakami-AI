<?php

namespace App\Http\Middleware;

use App\Models\Account\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\InteractsWithTime;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class IdleLogoutTimeout
{
    use InteractsWithTime;
    /**
     * The encrypter implementation.
    *
    * @var \Illuminate\Contracts\Encryption\Encrypter
    */
    protected $encrypter;

    protected $tokenName = 'X-TOKEN-IDLE';
    protected $lifeTime = 10;

    /**
     * Create a new middleware instance.
    *
    * @param  \Illuminate\Contracts\Encryption\Encrypter  $encrypter
    * @return void
    */
    public function __construct(Encrypter $encrypter)
    {
        $this->encrypter = $encrypter;
    }

    /**
     * Handle an incoming request.
    *
    * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
    */
    public function handle(Request $request, Closure $next, $force = 'true'): Response
    {
        $logoutTime = 30; //ba()?->logout_time;
        if (config('app.env') === 'development') {
            $logoutTime = 100;
        }
        $lastUrl = app('url')->previous();
        $allowedRoute = [
        ];
        if ($logoutTime && !is_request_in($allowedRoute, $lastUrl)) {
            $this->lifeTime = $logoutTime;
            if ($this->tokensMatch($request)) {
                return tap($next($request), function ($response) use ($request) {
                        $this->addCookieToResponse($request, $response);
                });
            }

            if ($force === 'true') {
                $request->session()->flush();
                return to_route('auth.login.index')->with(['error' => 'Your session has expired']);
            }
        }
        $this->lifeTime = 1000;
        return tap($next($request), function ($response) use ($request) {
            $this->addCookieToResponse($request, $response);
        });
    }
    protected function tokensMatch($request)
    {
        $token = $request->cookie($this->tokenName);

        return is_string($request->session()->token()) &&
            is_string($token) &&
            hash_equals($request->session()->token(), $token);
    }


    protected function addCookieToResponse($request, $response)
    {
        $config = config('session');
        $response->headers->setCookie($this->newCookie($request, $config));
        return $response;
    }

    protected function newCookie($request, $config)
    {
        return new Cookie(
            $this->tokenName,
            $request->session()->token(),
            date('Y-m-d H:i:s', strtotime("+{$this->lifeTime} min")),
            $config['path'],
            $config['domain'],
            $config['secure'],
            false,
            false,
            $config['same_site'] ?? null
        );
    }
}
