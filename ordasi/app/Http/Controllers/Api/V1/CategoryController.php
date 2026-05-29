<?php

namespace App\Http\Controllers\Api\V1;

use App\Category;
use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreRequest;
use App\Http\Requests\Category\UpdateRequest;
use App\Http\Resources\CategoryResource;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:categories.index')->only(['index', 'show']);
        $this->middleware('can:categories.create')->only(['store']);
        $this->middleware('can:categories.edit')->only(['update']);
        $this->middleware('can:categories.destroy')->only(['destroy']);
    }

    public function index()
    {
        return CategoryResource::collection(Category::latest()->paginate(20));
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
