<?php

namespace App\Http\Controllers\Api\V1;

use App\Product;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreRequest;
use App\Http\Requests\Product\UpdateRequest;
use App\Http\Resources\ProductResource;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:products.index')->only(['index', 'show']);
        $this->middleware('can:products.create')->only(['store']);
        $this->middleware('can:products.edit')->only(['update']);
        $this->middleware('can:products.destroy')->only(['destroy']);
    }

    public function index()
    {
        return ProductResource::collection(
            Product::with(['category', 'provider'])->latest()->paginate(20)
        );
    }

    public function store(StoreRequest $request)
    {
        $product = Product::create($request->all());

        // Si no se envió código, generar uno con el id (8 dígitos), igual que el admin.
        if (! $request->filled('code')) {
            $product->update(['code' => str_pad($product->id, 8, '0', STR_PAD_LEFT)]);
        }

        return new ProductResource($product->load(['category', 'provider']));
    }

    public function show(Product $product)
    {
        return new ProductResource($product->load(['category', 'provider']));
    }

    public function update(UpdateRequest $request, Product $product)
    {
        $product->update($request->all());
        return new ProductResource($product->load(['category', 'provider']));
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(null, 204);
    }
}
