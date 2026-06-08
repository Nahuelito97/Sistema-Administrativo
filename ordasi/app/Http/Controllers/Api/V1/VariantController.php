<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\ScopesToSeller;
use App\Http\Controllers\Controller;
use App\Http\Resources\VariantResource;
use App\Product;
use App\ProductVariant;
use Illuminate\Http\Request;

class VariantController extends Controller
{
    use ScopesToSeller;

    public function __construct()
    {
        $this->middleware('can:products.index')->only(['index']);
        $this->middleware('can:products.create')->only(['store']);
        $this->middleware('can:products.edit')->only(['update']);
        $this->middleware('can:products.destroy')->only(['destroy']);
    }

    public function index(Request $request, Product $product)
    {
        $this->assertOwned($product, $request);
        return VariantResource::collection($product->variants()->get());
    }

    public function store(Request $request, Product $product)
    {
        $this->assertOwned($product, $request);
        $data = $this->validateData($request);
        $variant = $product->variants()->create($data);
        return new VariantResource($variant);
    }

    public function update(Request $request, ProductVariant $variant)
    {
        $this->assertOwned($variant->product, $request);
        $variant->update($this->validateData($request));
        return new VariantResource($variant);
    }

    public function destroy(Request $request, ProductVariant $variant)
    {
        $this->assertOwned($variant->product, $request);
        $variant->delete();
        return response()->json(null, 204);
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'attributes' => ['nullable', 'array'],
            'sku'        => ['nullable', 'string', 'max:60'],
            'price'      => ['required', 'numeric', 'min:0'],
            'stock'      => ['required', 'integer', 'min:0'],
        ]);
    }
}
