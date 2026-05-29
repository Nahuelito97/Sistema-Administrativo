<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TagResource;
use App\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TagController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:tags.index')->only(['index', 'show']);
        $this->middleware('can:tags.create')->only(['store']);
        $this->middleware('can:tags.edit')->only(['update']);
        $this->middleware('can:tags.destroy')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Tag::query();
        if ($s = $request->query('search')) {
            $query->where('name', 'like', "%{$s}%");
        }
        $perPage = min((int) $request->query('per_page', 20) ?: 20, 1000);
        return TagResource::collection($query->latest()->paginate($perPage));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:tags,name'],
            'description' => ['nullable', 'string'],
        ]);
        $data['slug'] = Str::slug($data['name']) . '-' . uniqid();
        return new TagResource(Tag::create($data));
    }

    public function show(Tag $tag)
    {
        return new TagResource($tag);
    }

    public function update(Request $request, Tag $tag)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255', Rule::unique('tags', 'name')->ignore($tag->id)],
            'description' => ['nullable', 'string'],
        ]);
        $tag->update($data);
        return new TagResource($tag);
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();
        return response()->json(null, 204);
    }
}
