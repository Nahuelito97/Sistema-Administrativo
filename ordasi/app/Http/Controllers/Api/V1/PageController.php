<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:settings.edit')->only(['store', 'update', 'destroy']);
        // index/show públicos
    }

    /** Listado público (para el footer): solo título + slug de las publicadas. */
    public function index()
    {
        return response()->json([
            'data' => Page::where('is_published', true)->orderBy('order_column')->orderBy('id')
                ->get(['id', 'slug', 'title']),
        ]);
    }

    /** Listado admin (todas). */
    public function adminIndex()
    {
        return response()->json(['data' => Page::orderBy('order_column')->orderBy('id')->get()]);
    }

    public function show(Page $page)
    {
        abort_unless($page->is_published, 404);
        return response()->json(['data' => $page]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['slug'] = Str::slug($data['title']) . '-' . Str::random(4);
        return response()->json(['data' => Page::create($data)], 201);
    }

    public function update(Request $request, Page $page)
    {
        $page->update($this->validateData($request));
        return response()->json(['data' => $page]);
    }

    public function destroy(Page $page)
    {
        $page->delete();
        return response()->json(null, 204);
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'content'      => ['nullable', 'string'],
            'is_published' => ['boolean'],
            'order_column' => ['nullable', 'integer'],
        ]);
    }
}
