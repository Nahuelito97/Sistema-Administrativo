<?php

namespace App\Http\Controllers\Api\V1;

use App\Company;
use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\ProductResource;
use App\Product;
use Illuminate\Http\Request;

class PublicCatalogController extends Controller
{
    /** Productos visibles en la tienda (SHOP o BOTH, activos) con filtros. */
    public function products(Request $request)
    {
        $query = Product::query()
            ->whereIn('visibility', ['SHOP', 'BOTH'])
            ->where('status', 'ACTIVE')
            ->with(['category', 'brand', 'company', 'promotions', 'images', 'offers']);

        if ($company = $request->query('company_id')) {
            $query->where('company_id', $company);
        }

        if ($s = $request->query('search')) {
            $query->where('name', 'like', "%{$s}%");
        }
        foreach (['category_id', 'subcategory_id', 'brand_id'] as $f) {
            if ($v = $request->query($f)) {
                $query->where($f, $v);
            }
        }
        if ($min = $request->query('min_price')) {
            $query->where('sell_price', '>=', $min);
        }
        if ($max = $request->query('max_price')) {
            $query->where('sell_price', '<=', $max);
        }

        match ($request->query('sort')) {
            'price_asc'  => $query->orderBy('sell_price'),
            'price_desc' => $query->orderByDesc('sell_price'),
            'name'       => $query->orderBy('name'),
            default      => $query->latest(),
        };

        $perPage = min((int) $request->query('per_page', 12) ?: 12, 60);
        return ProductResource::collection($query->paginate($perPage));
    }

    /** Ficha pública del producto por slug. */
    public function product(Product $product)
    {
        abort_unless(in_array($product->visibility, ['SHOP', 'BOTH']) && $product->status === 'ACTIVE', 404);
        $product->increment('views');
        return new ProductResource($product->load(['category', 'subcategory', 'brand', 'company', 'images', 'variants', 'caracteristics', 'promotions', 'ratings.user']));
    }

    /** Productos relacionados (misma categoría) + más de la misma tienda. */
    public function related(Product $product)
    {
        $base = Product::whereIn('visibility', ['SHOP', 'BOTH'])->where('status', 'ACTIVE')->where('id', '!=', $product->id);

        $related = (clone $base)->where('category_id', $product->category_id)
            ->with(['brand', 'company', 'promotions', 'images'])->latest()->limit(8)->get();

        $fromShop = (clone $base)->where('company_id', $product->company_id)
            ->with(['brand', 'company', 'promotions', 'images'])->latest()->limit(8)->get();

        return response()->json([
            'related'   => ProductResource::collection($related),
            'from_shop' => ProductResource::collection($fromShop),
        ]);
    }

    /** Productos más vistos (para el home). */
    public function mostViewed()
    {
        $products = Product::whereIn('visibility', ['SHOP', 'BOTH'])->where('status', 'ACTIVE')
            ->with(['brand', 'company', 'promotions', 'images'])
            ->orderByDesc('views')->limit(8)->get();
        return ProductResource::collection($products);
    }

    /** Novedades: últimos productos publicados. */
    public function newest()
    {
        $products = Product::whereIn('visibility', ['SHOP', 'BOTH'])->where('status', 'ACTIVE')
            ->with(['brand', 'company', 'promotions', 'images', 'offers'])
            ->latest()->limit(8)->get();
        return ProductResource::collection($products);
    }

    /** Tiendas destacadas: activas con más productos visibles. */
    public function featuredStores()
    {
        $companies = Company::active()
            ->withCount(['products' => fn ($q) => $q->whereIn('visibility', ['SHOP', 'BOTH'])->where('status', 'ACTIVE')])
            ->orderByDesc('products_count')->limit(6)->get();
        return CompanyResource::collection($companies);
    }

    public function categories()
    {
        return CategoryResource::collection(\App\Category::orderBy('name')->get());
    }

    public function brands()
    {
        return BrandResource::collection(\App\Brand::orderBy('name')->get());
    }

    /** Tiendas activas (listado público del marketplace). */
    public function companies(Request $request)
    {
        $query = Company::active()
            ->search($request->query('search'))
            ->withCount(['products' => fn ($q) => $q->whereIn('visibility', ['SHOP', 'BOTH'])->where('status', 'ACTIVE')]);

        $perPage = min((int) $request->query('per_page', 24) ?: 24, 60);
        return CompanyResource::collection($query->latest()->paginate($perPage));
    }

    /** Perfil público de una tienda + sus productos visibles. */
    public function company(Company $company)
    {
        abort_unless($company->status === 'active', 404);

        $products = $company->products()
            ->whereIn('visibility', ['SHOP', 'BOTH'])
            ->where('status', 'ACTIVE')
            ->with(['category', 'brand', 'promotions', 'images'])
            ->latest()
            ->paginate(min((int) request()->query('per_page', 12) ?: 12, 60));

        return (new CompanyResource($company->loadCount('products')))
            ->additional(['reputation' => $company->reputation(), 'products' => [
                'data' => ProductResource::collection($products->items()),
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'last_page'    => $products->lastPage(),
                    'per_page'     => $products->perPage(),
                    'total'        => $products->total(),
                ],
            ]]);
    }
}
