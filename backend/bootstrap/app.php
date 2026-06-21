<?php

use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\SetLocale;
use Iamfarhad\Prometheus\Support\PrometheusMiddlewareHelper;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        PrometheusMiddlewareHelper::register($middleware);
        $middleware->web([SetLocale::class]);
        $middleware->alias(['admin' => IsAdmin::class]);
        $middleware->redirectGuestsTo(function (Request $request) {
            return $request->is('admin*') ? route('admin.login') : route('login');
        });
        $middleware->redirectUsersTo(function (Request $request) {
            $isAdmin = (bool) Auth::user()?->is_admin;

            if ($request->is('admin*') || $isAdmin) {
                return route('admin.dashboard');
            }

            return route('dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('cleanup:temp-files')
            ->everyTenMinutes()
            ->withoutOverlapping(30)
            ->runInBackground();

        $schedule->command('cleanup:videos')
            ->everyFifteenMinutes()
            ->withoutOverlapping(30)
            ->runInBackground();

        $schedule->command('videos:delete-blocked')
            ->dailyAt('00:00')
            ->withoutOverlapping(60)
            ->runInBackground();

        $schedule->command('alerts:clean-resolved')
            ->daily()
            ->withoutOverlapping(60)
            ->runInBackground();
    })
    ->create();
