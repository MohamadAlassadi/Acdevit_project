<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
->withRouting(
    web: __DIR__.'/../routes/web.php',
    commands: __DIR__.'/../routes/console.php',
    api: __DIR__.'/../routes/api.php', // ✅ أضف هذا السطر
    health: '/up',
)
->withMiddleware(function (Middleware $middleware) {
    
    $middleware->append(\Illuminate\Http\Middleware\HandleCors::class); // 👈 أضف هذا
})

    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
