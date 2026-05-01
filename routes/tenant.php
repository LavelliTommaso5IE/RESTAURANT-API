<?php

declare(strict_types=1);

use App\Http\Controllers\Central\TenantController;
use App\Http\Controllers\Tenant\Access\PermissionController;
use App\Http\Controllers\Tenant\Access\UserController;
use Illuminate\Support\Facades\Route;
//use PHPUnit\Framework\Attributes\Group;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Controllers\Tenant\Auth\AuthController;
use App\Http\Controllers\Tenant\Access\RoleController;
use App\Http\Controllers\Tenant\CategoryController;
use App\Http\Middleware\JwtMiddleware;

Route::middleware([
    'api', //estensione del file api.php
    //switch automatico sul DB del tenant in base al dominio
    InitializeTenancyByDomain::class, 
    //impedisce l'accesso alle rotte dei tenant se ci si trova sul dominio principale
    PreventAccessFromCentralDomains::class, 
])
    ->prefix('api')
    ->group(function () {
        //ROTTE PUBBLICHE (NON PROTETTE DA JWT)
        //rotta per verificare se il tenant esiste
        Route::get('/check-tenant', [TenantController::class, 'checkTenant']);
        //rotta per autenticazione dell'utente sul tenant
        Route::post('/login', [AuthController::class, 'login']);
        //rotta per il logout dell'utente (rimuove dal DB il refresh e dai cookie tutti i token)
        Route::get("/logout", [AuthController::class, 'logout'])
            ->middleware("jwt:false"); //evito che il middleware mi generi un nuovo access token


        // --- ROTTE PRIVATE (Protette da JWT) ---
        Route::middleware("jwt")->group(function () {
            //tutte queste rotte passano per il middleware JWT, che verifica l'access token
            // e, se scaduto, prova a rinnovarlo con il refresh token

            Route::get('/me', [AuthController::class, 'me']);

            // GESTIONE UTENTI (Raggruppate per Controller e Prefisso)
            // seleziona automaticamente il controller UserController
            Route::controller(UserController::class)
                ->prefix('users')
                ->group(function () {

                // Lista tutti gli utenti
                Route::get("/", "index")
                    //chiama il middleware dei permessi, che verifica se l'utente possiede il/i
                    //permessi necessari per accedere a questa rotta (passati come argomento)
                    ->middleware("permission:view_users");

                // 1. ROTTA CREAZIONE UTENTE
                // Endpoint: POST api/users/
                Route::post("/", "createUser")
                    ->middleware("permission:edit_users");

                // Aggiorna un utente esistente (Nota il plurale /users/{id})
                // Model Binding: {user} indica che Laravel cercherà automaticamente
                // l'utente nel database tramite l'ID fornito nell'URL (restituisce l'oggetto)
                Route::put("/{user}", "updateUser")
                    ->middleware("permission:edit_users");

                // Elimina un utente
                Route::delete("/{user}", "deleteUser")
                    ->middleware("permission:edit_users");
            });

            // GESTIONE RUOLI E PERMESSI (Raggruppate per Controller e Prefisso)
            // seleziona automaticamente il controller RoleController
            Route::controller(RoleController::class)
                ->prefix('roles')
                ->group(function () {
                    Route::get("/", "index")
                        ->middleware("permission:view_roles");

                    Route::get("/{role}", "show")
                        ->middleware("permission:view_roles");

                    Route::post("/", "store")
                        ->middleware("permission:edit_roles");

                    Route::put("/{role}", "update")
                        ->middleware("permission:edit_roles");

                    Route::delete("/{role}", "destroy")
                        ->middleware("permission:edit_roles");
    
                    Route::put("/{role}/permissions", "assignPermissions")
                        ->middleware("permission:edit_roles");
                });

            Route::controller(PermissionController::class)
                ->prefix("permissions")
                ->group(function () {

                    Route::get("/", "index")
                        ->middleware("permission:view_permissions");
                });

            Route::controller(CategoryController::class)
                ->prefix("categories")
                ->group(function () {
                    Route::get("/", "index")
                        ->middleware("permission:view_categories");

                    Route::get("/{category}", "show")
                        ->middleware("permission:view_categories");

                    Route::post("/", "store")
                        ->middleware("permission:edit_categories");

                    Route::put("/{category}", "update")
                        ->middleware("permission:edit_categories");

                    Route::delete("/{category}", "destroy")
                        ->middleware("permission:edit_categories");
                });

            Route::controller(\App\Http\Controllers\Tenant\ProductController::class)
                ->prefix("products")
                ->group(function () {
                    Route::get("/", "index")
                        ->middleware("permission:view_products");

                    Route::get("/{product}", "show")
                        ->middleware("permission:view_products");

                    Route::post("/", "store")
                        ->middleware("permission:edit_products");

                    Route::put("/{product}", "update")
                        ->middleware("permission:edit_products");

                    Route::delete("/{product}", "destroy")
                        ->middleware("permission:edit_products");
                });

            Route::controller(\App\Http\Controllers\Tenant\DishController::class)
                ->prefix("dishes")
                ->group(function () {
                    Route::get("/", "index")
                        ->middleware("permission:view_dishes");

                    Route::get("/{dish}", "show")
                        ->middleware("permission:view_dishes");

                    Route::post("/", "store")
                        ->middleware("permission:edit_dishes");

                    Route::put("/{dish}", "update")
                        ->middleware("permission:edit_dishes");

                    Route::delete("/{dish}", "destroy")
                        ->middleware("permission:edit_dishes");
                });

            Route::controller(\App\Http\Controllers\Tenant\MenuController::class)
                ->prefix("menus")
                ->group(function () {
                    Route::get("/", "index")
                        ->middleware("permission:view_menus");

                    Route::get("/{menu}", "show")
                        ->middleware("permission:view_menus");

                    Route::post("/", "store")
                        ->middleware("permission:edit_menus");

                    Route::put("/{menu}", "update")
                        ->middleware("permission:edit_menus");

                    Route::delete("/{menu}", "destroy")
                        ->middleware("permission:edit_menus");
                });

            Route::controller(\App\Http\Controllers\Tenant\TableController::class)
                ->prefix("tables")
                ->group(function () {
                    Route::get("/", "index")
                        ->middleware("permission:view_tables");

                    Route::get("/{table}", "show")
                        ->middleware("permission:view_tables");

                    Route::post("/", "store")
                        ->middleware("permission:edit_tables");

                    Route::put("/{table}", "update")
                        ->middleware("permission:edit_tables");

                    Route::delete("/{table}", "destroy")
                        ->middleware("permission:edit_tables");

                    // Metodi custom
                    Route::post("/{table}/join", "join")
                        ->middleware("permission:edit_tables");

                    Route::post("/{table}/separate", "separate")
                        ->middleware("permission:edit_tables");

                    Route::post("/{table}/generate-pin", "generatePin")
                        ->middleware("permission:edit_tables");

                    Route::post("/{table}/clear", "clearTable")
                        ->middleware("permission:edit_tables");
                });

            Route::controller(\App\Http\Controllers\Tenant\CustomerController::class)
                ->prefix("customers")
                ->group(function () {
                    Route::get("/", "index")
                        ->middleware("permission:view_customers");

                    Route::get("/{customer}", "show")
                        ->middleware("permission:view_customers");

                    Route::post("/", "store")
                        ->middleware("permission:edit_customers");

                    Route::put("/{customer}", "update")
                        ->middleware("permission:edit_customers");

                    Route::delete("/{customer}", "destroy")
                        ->middleware("permission:edit_customers");

                    Route::get("/{customer}/reservations", "reservations")
                        ->middleware("permission:view_reservations");
                });

            Route::controller(\App\Http\Controllers\Tenant\ReservationController::class)
                ->prefix("reservations")
                ->group(function () {
                    Route::get("/", "index")
                        ->middleware("permission:view_reservations");

                    Route::get("/{reservation}", "show")
                        ->middleware("permission:view_reservations");

                    Route::post("/", "store")
                        ->middleware("permission:edit_reservations");

                    Route::put("/{reservation}", "update")
                        ->middleware("permission:edit_reservations");

                    Route::delete("/{reservation}", "destroy")
                        ->middleware("permission:edit_reservations");
                });
        });

        // ROTTE PUBBLICHE (Fuori da JWT, ma dentro al tenant)
        Route::controller(\App\Http\Controllers\Tenant\PublicMenuController::class)
            ->prefix("public/menus")
            ->group(function () {
                Route::get("/", "index");
                Route::get("/{menu}", "show");
            });
    });