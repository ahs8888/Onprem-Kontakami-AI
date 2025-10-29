<?php

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use App\Http\Middleware\HandleAppearance;
use Illuminate\Console\Scheduling\Schedule;
use App\Http\Middleware\HandleInertiaRequests;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Load modular admin routes
            Route::middleware('web')
                ->group(base_path('routes/admin/recording.php'));
            
            Route::middleware('web')
                ->group(base_path('routes/admin/ticket.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class
        ]);

        $middleware->alias([
            'me-auth' => \App\Http\Middleware\AuthenticationMiddleware::class,
            'idle-logout' =>  \App\Http\Middleware\IdleLogoutTimeout::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
            // if (! app()->environment(['local', 'testing']) && in_array($response->getStatusCode(), [500, 503, 404, 403])) {
            //     return Inertia::render('Error/Index', ['status' => $response->getStatusCode()])
            //         ->toResponse($request)
            //         ->setStatusCode($response->getStatusCode());
            // } elseif ($response->getStatusCode() === 419) {
            //     return back()->with([
            //         'message' => 'The page expired, please try again.',
            //     ]);
            // }

            if ($response->getStatusCode() === 419) {
                return back()->with([
                    'message' => 'The page expired, please try again.',
                ]);
            }

            return $response;
        });
    })->withSchedule(function (Schedule $schedule) {
        $schedule->command("transcript:dispatch")->everyMinute();
    })->create();
