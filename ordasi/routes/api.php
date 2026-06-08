<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BrandController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CurrencyController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\SettingController;
use App\Http\Controllers\Api\V1\SliderController;
use App\Http\Controllers\Api\V1\SocialMediaController;
use App\Http\Controllers\Api\V1\SubscriptionController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\PublicCatalogController;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\PromotionController;
use App\Http\Controllers\Api\V1\RatingController;
use App\Http\Controllers\Api\V1\TagController;
use App\Http\Controllers\Api\V1\BusinessController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CompanyController;
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
    Route::post('register', [AuthController::class, 'register'])->name('api.register');

    // Catálogo público (storefront)
    Route::get('public/products', [PublicCatalogController::class, 'products'])->name('public.products');
    Route::get('public/products/{product:slug}', [PublicCatalogController::class, 'product'])->name('public.product');
    Route::get('public/categories', [PublicCatalogController::class, 'categories'])->name('public.categories');
    Route::get('public/brands', [PublicCatalogController::class, 'brands'])->name('public.brands');
    // Tiendas del marketplace (storefront)
    Route::get('public/companies', [PublicCatalogController::class, 'companies'])->name('public.companies');
    Route::get('public/companies/{company:slug}', [PublicCatalogController::class, 'company'])->name('public.company');
    // Webhook de MercadoPago (lo llama MP, sin token)
    Route::post('webhooks/mercadopago', [PaymentController::class, 'webhook'])->name('webhooks.mercadopago');

    // Blog público (storefront)
    Route::get('blog', [PostController::class, 'publicIndex'])->name('blog.index');
    Route::get('blog/{post:slug}', [PostController::class, 'publicShow'])->name('blog.show');

    // Contenido público del storefront
    Route::get('public/sliders', [SliderController::class, 'publicIndex'])->name('public.sliders');
    Route::get('public/social-media', [SocialMediaController::class, 'publicIndex'])->name('public.social');
    Route::get('public/settings', [SettingController::class, 'publicIndex'])->name('public.settings');
    Route::post('subscribe', [SubscriptionController::class, 'store'])->name('subscribe');

    // Protegido por token Sanctum (los permisos can: se aplican en cada controller)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me'])->name('api.me');
        Route::post('logout', [AuthController::class, 'logout'])->name('api.logout');

        // Tiendas (marketplace, admin)
        Route::post('companies/{company}/logo', [CompanyController::class, 'uploadLogo'])->name('companies.logo');
        Route::post('companies/{company}/banner', [CompanyController::class, 'uploadBanner'])->name('companies.banner');
        Route::apiResource('companies', CompanyController::class);

        // Catálogo
        Route::apiResource('categories', CategoryController::class);
        Route::apiResource('subcategories', SubcategoryController::class);
        Route::apiResource('brands', BrandController::class);
        Route::apiResource('promotions', PromotionController::class);
        Route::apiResource('providers', ProviderController::class);
        // Reseñas de producto
        Route::get('products/{product}/ratings', [RatingController::class, 'index'])->name('products.ratings.index');
        Route::post('products/{product}/ratings', [RatingController::class, 'store'])->name('products.ratings.store');
        // Blog y etiquetas (admin)
        Route::apiResource('tags', TagController::class);
        Route::post('posts/{post}/image', [PostController::class, 'uploadImage'])->name('posts.image');
        Route::apiResource('posts', PostController::class);

        // Contenido del sitio (admin)
        Route::post('sliders/{slider}/image', [SliderController::class, 'uploadImage'])->name('sliders.image');
        Route::apiResource('sliders', SliderController::class);
        Route::apiResource('social-media', SocialMediaController::class)->parameters(['social-media' => 'social']);
        Route::get('subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::delete('subscriptions/{subscription}', [SubscriptionController::class, 'destroy'])->name('subscriptions.destroy');
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
        Route::apiResource('currencies', CurrencyController::class);

        // Perfil del usuario autenticado
        Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
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
