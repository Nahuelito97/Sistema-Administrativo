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
        $query = Category::query();
        if ($s = $request->query('search')) {
            $query->where('name', 'like', "%{$s}%");
        }
        $perPage = min((int) $request->query('per_page', 20) ?: 20, 1000);
        return CategoryResource::collection($query->latest()->paginate($perPage));
    }

    public function store(StoreRequest $request)
    {
        return new CategoryResource(Category::create($request->all()));
    }

    public function show(Category $category)
    {
        return new CategoryResource($category);
    }

    public function update(UpdateRequest $request, Category $category)
    {
        $category->update($request->all());
        return new CategoryResource($category);
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(null, 204);
    }
}
