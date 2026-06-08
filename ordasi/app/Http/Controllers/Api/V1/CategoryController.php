<?php

namespace App\Http\Controllers\Api\V1;

use App\Category;
use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreRequest;
use App\Http\Requests\Category\UpdateRequest;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:categories.index')->only(['index', 'show']);
        $this->middleware('can:categories.create')->only(['store']);
        $this->middleware('can:categories.edit')->only(['update']);
        $this->middleware('can:categories.destroy')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Category::with('parent');
        if ($s = $request->query('search')) {
            $query->where('name', 'like', "%{$s}%");
        }
        $perPage = min((int) $request->query('per_page', 20) ?: 20, 1000);
        return CategoryResource::collection($query->orderBy('name')->paginate($perPage));
    }

    /** Árbol completo de categorías (raíces con hijos anidados). */
    public function tree()
    {
        return CategoryResource::collection(
            Category::roots()->with('childrenRecursive')->orderBy('name')->get()
        );
    }

    public function store(StoreRequest $request)
    {
        $data = $request->all();
        $request->validate(['parent_id' => ['nullable', 'exists:categories,id']]);
        return new CategoryResource(Category::create($data)->load('parent'));
    }

    public function show(Category $category)
    {
        return new CategoryResource($category->load('parent', 'childrenRecursive'));
    }

    public function update(UpdateRequest $request, Category $category)
    {
        $request->validate(['parent_id' => ['nullable', 'exists:categories,id', 'not_in:' . $category->id]]);
        $category->update($request->all());
        return new CategoryResource($category->load('parent'));
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(null, 204);
    }
}
