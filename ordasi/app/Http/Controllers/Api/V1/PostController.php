<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Post;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:posts.index')->only(['index']);
        $this->middleware('can:posts.show')->only(['show']);
        $this->middleware('can:posts.create')->only(['store', 'uploadImage']);
        $this->middleware('can:posts.edit')->only(['update', 'uploadImage']);
        $this->middleware('can:posts.destroy')->only(['destroy']);
    }

    // ---------- Admin ----------
    public function index(Request $request)
    {
        $query = Post::with('category');
        if ($s = $request->query('search')) {
            $query->where('title', 'like', "%{$s}%");
        }
        $perPage = min((int) $request->query('per_page', 20) ?: 20, 1000);
        return PostResource::collection($query->latest()->paginate($perPage));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['slug'] = $this->uniqueSlug($data['title']);
        if (($data['status'] ?? 'DRAFT') === 'PUBLISHED' && empty($data['published_at'])) {
            $data['published_at'] = Carbon::now();
        }
        $post = Post::create($data);
        $post->tags()->sync($request->input('tag_ids', []));
        return new PostResource($post->load('category', 'tags'));
    }

    public function show(Post $post)
    {
        return new PostResource($post->load('category', 'tags'));
    }

    public function update(Request $request, Post $post)
    {
        $data = $this->validateData($request);
        if ($data['title'] !== $post->title) {
            $data['slug'] = $this->uniqueSlug($data['title'], $post->id);
        }
        if ($data['status'] === 'PUBLISHED' && ! $post->published_at && empty($data['published_at'])) {
            $data['published_at'] = Carbon::now();
        }
        $post->update($data);
        $post->tags()->sync($request->input('tag_ids', []));
        return new PostResource($post->load('category', 'tags'));
    }

    public function destroy(Post $post)
    {
        $post->delete();
        return response()->json(null, 204);
    }

    public function uploadImage(Request $request, Post $post)
    {
        $request->validate(['image' => ['required', 'image', 'max:4096']]);
        $post->update(['image' => $request->file('image')->store('posts', 'public')]);
        return new PostResource($post->load('category', 'tags'));
    }

    // ---------- Público (storefront / blog) ----------
    public function publicIndex(Request $request)
    {
        $query = Post::published()->with('category')->latest('published_at');
        if ($s = $request->query('search')) {
            $query->where('title', 'like', "%{$s}%");
        }
        return PostResource::collection($query->paginate(9));
    }

    public function publicShow(Post $post)
    {
        abort_unless($post->status === 'PUBLISHED', 404);
        return new PostResource($post->load('category', 'tags'));
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'status'       => ['required', 'in:DRAFT,PUBLISHED'],
            'excerpt'      => ['nullable', 'string', 'max:1000'],
            'body'         => ['nullable', 'string'],
            'published_at' => ['nullable', 'date'],
            'category_id'  => ['nullable', 'exists:categories,id'],
            'tag_ids'      => ['array'],
            'tag_ids.*'    => ['exists:tags,id'],
        ]);
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'post';
        $slug = $base;
        $i = 1;
        while (Post::withTrashed()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
