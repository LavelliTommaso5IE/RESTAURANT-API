<?php

declare(strict_types=1);

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Middleware\JwtMiddleware;

Route::middleware([
    'api',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])
->prefix('api')
->group(function () {

    // --- ROTTE PUBBLICHE ---
    Route::post('/login', [AuthController::class, 'login']);

    // --- ROTTE PRIVATE (Protette da JWT) ---
    Route::middleware([JwtMiddleware::class])->group(function () {

        Route::get('/me', [AuthController::class, 'me']);

        // GESTIONE UTENTI (Raggruppate per Controller e Prefisso)
        Route::controller(UserController::class)
            ->prefix('users')
            ->group(function () {
                
                // Lista tutti gli utenti
                Route::get("/", "index")
                    ->middleware("permission:view_users");

                // Crea un nuovo utente
                Route::post("/", "createUser")
                    ->middleware("permission:edit_users");

                // Aggiorna un utente esistente (Nota il plurale /users/{id})
                Route::put("/{user}", "updateUser")
                    ->middleware("permission:edit_users");

                // Elimina un utente
                Route::delete("/{user}", "deleteUser")
                    ->middleware("permission:edit_users");
        });

        Route::controller(RoleController::class)
            ->prefix('roles')
            ->group(function () {
                
                Route::get("/", "index")
                    ->middleware("permission:view_roles");

                Route::get("/{role}", "show")
                    ->middleware("permission:view_roles");

                // CORRETTO: punta alla funzione store()
                Route::post("/", "store") 
                    ->middleware("permission:edit_roles");

                Route::put("/{role}", "update")
                    ->middleware("permission:edit_roles");

                // CORRETTO: punta alla funzione destroy()
                Route::delete("/{role}", "destroy") 
                    ->middleware("permission:edit_roles");
        });
    });
});