<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Central\TenantController; // <-- Importiamo il Controller!

Route::get('/ping', function (Request $request) {
    return response()->json(['message' => 'Api centrale']);
});

// GUARDA CHE PULIZIA! 
// Diciamo a Laravel: "Quando arriva una POST a /create-tenant, manda tutto al metodo 'store' del TenantController"
Route::post('/create-tenant', [TenantController::class, 'store']);