<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubcategoryResource;
use App\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubcategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:subcategories.index')->only(['index', 'show']);
        $this->middleware('can:subcategories.create')->only(['store']);
        $this->middleware('can:subcategories.edit')->only(['update']);
        $this->middleware('can:subcategories.destroy')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Subcategory::with('category');
        if ($s = $request->query('search')) {
            $query->where('name', 'like', "%{$s}%");
        }
        if ($cat = $request->query('category_id')) {
            $query->where('category_id', $cat);
        }
        $perPage = min((int) $request->query('per_page', 20) ?: 20, 1000);
        return SubcategoryResource::collection($query->latest()->paginate($perPage));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name'        => ['required', 'string', 'max:255'],
        ]);
        $data['slug'] = Str::slug($data['name']) . '-' . uniqid();
        return new SubcategoryResource(Subcategory::create($data)->load('category'));
    }

    public function show(Subcategory $subcategory)
    {
        return new SubcategoryResource($subcategory->load('category'));
    }

    public function update(Request $request, Subcategory $subcategory)
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name'        => ['required', 'string', 'max:255'],
        ]);
        $subcategory->update($data);
        return new SubcategoryResource($subcategory->load('category'));
    }

    public function destroy(Subcategory $subcategory)
    {
        $subcategory->delete();
        return response()->json(null, 204);
    }
}
