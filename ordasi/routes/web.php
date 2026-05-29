<?php

use App\Http\Controllers\BusinessController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\UserController;
use App\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Reportes: deben ir ANTES del resource 'sales' para no chocar con sales/{sale}
Route::get('sales/reports_day', [ReportController::class, 'reports_day'])->name('reports.day');
Route::get('sales/reports_date', [ReportController::class, 'reports_date'])->name('reports.date');
Route::post('sales/report_results', [ReportController::class, 'report_results'])->name('report.results');

Route::resource('business', BusinessController::class)->names('business')->only([
    'index', 'update',
]);

Route::resource('categories', CategoryController::class)->names('categories');
Route::resource('clients', ClientController::class)->names('clients');
Route::resource('products', ProductController::class)->names('products');
Route::resource('providers', ProviderController::class)->names('providers');
Route::resource('purchases', PurchaseController::class)->names('purchases')->except([
    'edit', 'update', 'destroy',
]);
Route::resource('sales', SaleController::class)->names('sales')->except([
    'edit', 'update', 'destroy',
]);

Route::get('purchases/pdf/{purchase}', [PurchaseController::class, 'pdf'])->name('purchases.pdf');
Route::get('sales/pdf/{sale}', [SaleController::class, 'pdf'])->name('sales.pdf');
Route::get('sales/print/{sale}', [SaleController::class, 'print'])->name('sales.print');

Route::get('purchases/upload/{purchase}', [PurchaseController::class, 'upload'])->name('upload.purchases');

Route::get('change_status/products/{product}', [ProductController::class, 'change_status'])->name('change.status.products');
Route::get('change_status/purchases/{purchase}', [PurchaseController::class, 'change_status'])->name('change.status.purchases');
Route::get('change_status/sales/{sale}', [SaleController::class, 'change_status'])->name('change.status.sales');

Route::resource('users', UserController::class)->names('users');
Route::resource('roles', RoleController::class)->names('roles');

Route::get('get_products_by_barcode', [ProductController::class, 'get_products_by_barcode'])->name('get_products_by_barcode');
Route::get('get_products_by_id', [ProductController::class, 'get_products_by_id'])->name('get_products_by_id');
Route::get('print_barcode', [ProductController::class, 'print_barcode'])->name('print_barcode');

Route::get('/barcode', function () {
    $products = Product::get();
    return view('admin.product.barcode', compact('products'));
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');
