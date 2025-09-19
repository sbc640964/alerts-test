<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withSchedule(function (Schedule $schedule): void {
        //
        $schedule->call(function () {
            try {
                $res = Http::timeout(3)->get('https://www.oref.org.il/warningMessages/alert/Alerts.json');
                if($res->successful()) {
                    $json = $res->json();
                    $json && Log::error('New Alert', $json);
                }

                if($res->failed()) {
                    Log::error('Failed to fetch alerts'. $res->status());
                }
            } catch (Exception $e) {
                    Log::error('Error fetching alerts: '.$e->getMessage());
            }
        })
            ->name('Fetch Alerts')
            ->everyTwoSeconds();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
