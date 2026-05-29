<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BrandController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\BusinessController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\SubcategoryController;
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
    // Webhook de MercadoPago (lo llama MP, sin token)
    Route::post('webhooks/mercadopago', [PaymentController::class, 'webhook'])->name('webhooks.mercadopago');

    // Protegido por token Sanctum (los permisos can: se aplican en cada controller)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me'])->name('api.me');
        Route::post('logout', [AuthController::class, 'logout'])->name('api.logout');

        // Catálogo
        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('subcategories', SubcategoryController::class);
        Route::apiResource('brands', BrandController::class);
        Route::apiResource('providers', ProviderController::class);
        Route::get('products/barcodes/pdf', [ProductController::class, 'barcodesPdf'])->name('products.barcodes');
        Route::patch('products/{product}/status', [ProductController::class, 'changeStatus'])->name('products.status');
        Route::post('products/{product}/images', [ProductController::class, 'uploadImages'])->name('products.images.store');
        Route::delete('products/{product}/images/{image}', [ProductController::class, 'deleteImage'])->name('products.images.destroy');
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

        // Carrito (usuario autenticado)
        Route::get('cart', [CartController::class, 'show'])->name('cart.show');
        Route::post('cart/items', [CartController::class, 'addItem'])->name('cart.items.add');
        Route::patch('cart/items/{item}', [CartController::class, 'updateItem'])->name('cart.items.update');
        Route::delete('cart/items/{item}', [CartController::class, 'removeItem'])->name('cart.items.remove');
        Route::delete('cart', [CartController::class, 'clear'])->name('cart.clear');

        // Órdenes del cliente
        Route::post('orders', [OrderController::class, 'store'])->name('orders.store');
        Route::get('my-orders', [OrderController::class, 'myOrders'])->name('orders.mine');
        Route::get('my-orders/{order}', [OrderController::class, 'myShow'])->name('orders.mine.show');
        Route::post('orders/{order}/pay', [PaymentController::class, 'pay'])->name('orders.pay');

        // Órdenes (gestión admin)
        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');

        // Dashboard
        Route::get('stats', [StatsController::class, 'index'])->name('stats.index');
        Route::get('stats/sales-daily', [StatsController::class, 'salesDaily'])->name('stats.sales-daily');
    });
});
