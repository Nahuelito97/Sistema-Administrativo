<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProviderController;
use App\Http\Controllers\Api\V1\PurchaseController;
use App\Http\Controllers\Api\V1\SaleController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Público
    Route::post('login', [AuthController::class, 'login'])->name('api.login');

    // Protegido por token Sanctum (los permisos can: se aplican en cada controller)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me'])->name('api.me');
        Route::post('logout', [AuthController::class, 'logout'])->name('api.logout');

        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('providers', ProviderController::class);
        Route::apiResource('clients', ClientController::class);
        Route::apiResource('products', ProductController::class);

        // Ventas y compras: solo listar, ver y crear (igual que el panel admin)
        Route::apiResource('sales', SaleController::class)->only(['index', 'show', 'store']);
        Route::apiResource('purchases', PurchaseController::class)->only(['index', 'show', 'store']);
    });
});
