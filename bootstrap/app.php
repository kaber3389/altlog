<?php

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ModelNotFoundException|RecordsNotFoundException $e) {
            return response()->json([
                'message' => 'Resource not found',
            ], 404);
        });

        $exceptions->render(function (\App\Exceptions\ResourceConflictException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 409);
        });
    })->create();
