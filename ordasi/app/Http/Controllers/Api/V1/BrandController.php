<?php

namespace App\Http\Controllers\Api\V1;

use App\Brand;
use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:brands.index')->only(['index', 'show']);
        $this->middleware('can:brands.create')->only(['store']);
        $this->middleware('can:brands.edit')->only(['update']);
        $this->middleware('can:brands.destroy')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Brand::query();
        if ($s = $request->query('search')) {
            $query->where('name', 'like', "%{$s}%");
        }
        $perPage = min((int) $request->query('per_page', 20) ?: 20, 1000);
        return BrandResource::collection($query->latest()->paginate($perPage));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);
        $data['slug'] = $this->uniqueSlug($data['name']);
        return new BrandResource(Brand::create($data));
    }

    public function show(Brand $brand)
    {
        return new BrandResource($brand);
    }

    public function update(Request $request, Brand $brand)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);
        if ($data['name'] !== $brand->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $brand->id);
        }
        $brand->update($data);
        return new BrandResource($brand);
    }

    public function destroy(Brand $brand)
    {
        $brand->delete();
        return response()->json(null, 204);
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;
        while (Brand::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
