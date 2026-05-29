<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BusinessController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\PermissionController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProviderController;
use App\Http\Controllers\Api\V1\PurchaseController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\SaleController;
use App\Http\Controllers\Api\V1\StatsController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Público
    Route::post('login', [AuthController::class, 'login'])->name('api.login');

    // Protegido por token Sanctum (los permisos can: se aplican en cada controller)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me'])->name('api.me');
        Route::post('logout', [AuthController::class, 'logout'])->name('api.logout');

        // Catálogo
        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('providers', ProviderController::class);
        Route::apiResource('clients', ClientController::class);
        Route::get('products/barcodes/pdf', [ProductController::class, 'barcodesPdf'])->name('products.barcodes');
        Route::patch('products/{product}/status', [ProductController::class, 'changeStatus'])->name('products.status');
        Route::apiResource('products', ProductController::class);

        // Ventas y compras: solo listar, ver y crear (igual que el panel admin)
        Route::patch('sales/{sale}/status', [SaleController::class, 'changeStatus'])->name('sales.status');
        Route::get('sales/{sale}/pdf', [SaleController::class, 'pdf'])->name('sales.pdf');
        Route::apiResource('sales', SaleController::class)->only(['index', 'show', 'store']);

        Route::patch('purchases/{purchase}/status', [PurchaseController::class, 'changeStatus'])->name('purchases.status');
        Route::get('purchases/{purchase}/pdf', [PurchaseController::class, 'pdf'])->name('purchases.pdf');
        Route::post('purchases/{purchase}/comprobante', [PurchaseController::class, 'uploadComprobante'])->name('purchases.comprobante');
        Route::apiResource('purchases', PurchaseController::class)->only(['index', 'show', 'store']);

        // Administración
        Route::apiResource('users', UserController::class);
        Route::apiResource('roles', RoleController::class);
        Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');

        // Reportes
        Route::get('reports/sales', [ReportController::class, 'sales'])->name('reports.sales');

        // Empresa (registro único)
        Route::get('business', [BusinessController::class, 'show'])->name('business.show');
        Route::put('business', [BusinessController::class, 'update'])->name('business.update');

        // Dashboard
        Route::get('stats', [StatsController::class, 'index'])->name('stats.index');
    });
});
