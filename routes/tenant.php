<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'api',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,

    //Controlla se c'è il dominio, se c'è, allora carica le rotte di tenant.php, altrimenti carica quelle di api.php
])
    ->prefix('api')
    ->group(function () {

        Route::get('/status', function () {
            return response()->json(['message' => 'Tenant API funziona', 'tenant_id' => tenant('id')]);
        });
    });
