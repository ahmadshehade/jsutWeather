<?php

use App\Jobs\UpdateWeatherForFavorites;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule; // ✅ استخدم هذا النوع

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        channels: __DIR__.'/../routes/channels.php',
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function (Schedule $schedule) { // ✅ الآن النوع صحيح
        // تحديث الكاش كل ساعة
        $schedule->command('weather:update-cache')
            ->hourly()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/weather-schedule.log'));

        // تحديث بيانات الطقس للمدن المفضلة كل 5 دقائق
        $schedule->job(new UpdateWeatherForFavorites([]))
            ->everyFiveMinutes()
            ->withoutOverlapping();
    })
    ->create();
