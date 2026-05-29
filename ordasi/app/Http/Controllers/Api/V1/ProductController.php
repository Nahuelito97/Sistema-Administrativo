<?php

namespace App\Http\Controllers\Api\V1;

use App\Image;
use App\Product;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreRequest;
use App\Http\Requests\Product\UpdateRequest;
use App\Http\Resources\ProductResource;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public const RELATIONS = ['category', 'subcategory', 'provider', 'brand', 'images', 'promotions', 'ratings.user'];

    public function __construct()
    {
        $this->middleware('can:products.index')->only(['index', 'show']);
        $this->middleware('can:products.create')->only(['store', 'uploadImages']);
        $this->middleware('can:products.edit')->only(['update', 'uploadImages', 'deleteImage']);
        $this->middleware('can:products.destroy')->only(['destroy']);
        $this->middleware('can:change.status.products')->only(['changeStatus']);
    }

    public function index(Request $request)
    {
        $query = Product::with(['category', 'provider', 'brand', 'promotions']);
        if ($s = $request->query('search')) {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%"));
        }
        $perPage = min((int) $request->query('per_page', 20) ?: 20, 1000);
        return ProductResource::collection($query->latest()->paginate($perPage));
    }

    public function store(StoreRequest $request)
    {
        $product = Product::create($request->all() + ['slug' => $this->uniqueSlug($request->name)]);

        if (! $request->filled('code')) {
            $product->update(['code' => str_pad($product->id, 8, '0', STR_PAD_LEFT)]);
        }

        return new ProductResource($product->load(self::RELATIONS));
    }

    public function show(Product $product)
    {
        return new ProductResource($product->load(self::RELATIONS));
    }

    public function update(UpdateRequest $request, Product $product)
    {
        $data = $request->all();
        if ($request->filled('name') && $request->name !== $product->name) {
            $data['slug'] = $this->uniqueSlug($request->name, $product->id);
        }
        $product->update($data);
        return new ProductResource($product->load(self::RELATIONS));
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(null, 204);
    }

    /** Alterna ACTIVE / DEACTIVATED. */
    public function changeStatus(Product $product)
    {
        $product->update(['status' => $product->status === 'ACTIVE' ? 'DEACTIVATED' : 'ACTIVE']);
        return new ProductResource($product->load(['category', 'provider', 'brand']));
    }

    /** Sube una o varias imágenes a la galería del producto. */
    public function uploadImages(Request $request, Product $product)
    {
        $request->validate([
            'images'   => ['required', 'array', 'min:1'],
            'images.*' => ['image', 'max:4096'],
        ]);

        foreach ($request->file('images') as $file) {
            $path = $file->store('products', 'public');
            $product->images()->create(['url' => $path]);
        }

        return new ProductResource($product->load(self::RELATIONS));
    }

    /** Elimina una imagen de la galería. */
    public function deleteImage(Product $product, Image $image)
    {
        if ($image->imageable_id !== $product->id || $image->imageable_type !== Product::class) {
            abort(404);
        }
        $image->delete();
        return response()->json(null, 204);
    }

    /** PDF con los códigos de barra de todos los productos. */
    public function barcodesPdf()
    {
        $products = Product::get();
        $pdf = PDF::loadView('admin.product.barcode', compact('products'));
        return $pdf->download('codigos_de_barras.pdf');
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base ?: 'producto';
        $i = 1;
        while (Product::withTrashed()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
