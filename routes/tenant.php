<?php

declare(strict_types=1);

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\JwtMiddleware;

Route::middleware([
    'api',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])
    ->prefix('api')
    ->group(function () {

        // Rotta Pubblica
        Route::post('/login', [AuthController::class, 'login']);

        // Rotta Privata: Usiamo la classe direttamente invece della stringa 'jwt'!
        Route::middleware([JwtMiddleware::class])->group(function () {

            Route::get('/me', [AuthController::class, 'me']);

            Route::post("/users", [UserController::class, "createUser"]);
        });

    });