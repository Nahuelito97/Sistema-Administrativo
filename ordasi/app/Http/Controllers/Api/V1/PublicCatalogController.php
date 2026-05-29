<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Http\Resources\CategoryResource;
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
            ->with(['category', 'brand', 'promotions', 'images']);

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
        return new ProductResource($product->load(['category', 'subcategory', 'brand', 'images', 'promotions', 'ratings.user']));
    }

    public function categories()
    {
        return CategoryResource::collection(\App\Category::orderBy('name')->get());
    }

    public function brands()
    {
        return BrandResource::collection(\App\Brand::orderBy('name')->get());
    }
}
