<?php

namespace App\Http\Controllers\Api\V1;

use App\Caracteristic;
use App\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CaracteristicController extends Controller
{
    public function __construct()
    {
        // index es lectura pública (lo usa el form del vendedor); escritura requiere permiso.
        $this->middleware('can:categories.create')->only(['store']);
        $this->middleware('can:categories.destroy')->only(['destroy']);
    }

    /** Características definidas para una categoría. */
    public function index(Category $category)
    {
        return response()->json(['data' => $category->caracteristics()->orderBy('name')->get(['id', 'name'])]);
    }

    public function store(Request $request, Category $category)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255']]);
        $c = $category->caracteristics()->create($data);
        return response()->json(['data' => $c], 201);
    }

    public function destroy(Caracteristic $caracteristic)
    {
        $caracteristic->delete();
        return response()->json(null, 204);
    }
}
