<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Satu-satunya area web yang butuh login adalah halaman satpam
        // (panel admin Filament punya login sendiri).
        $middleware->redirectGuestsTo(fn () => route('satpam.login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
