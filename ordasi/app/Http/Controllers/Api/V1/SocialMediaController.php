<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SocialMediaResource;
use App\SocialMedia;
use Illuminate\Http\Request;

class SocialMediaController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:social.index')->only(['index', 'show']);
        $this->middleware('can:social.create')->only(['store']);
        $this->middleware('can:social.edit')->only(['update']);
        $this->middleware('can:social.destroy')->only(['destroy']);
    }

    public function index()
    {
        return SocialMediaResource::collection(SocialMedia::latest()->paginate(50));
    }

    /** Público: redes para el footer del storefront. */
    public function publicIndex()
    {
        return SocialMediaResource::collection(SocialMedia::all());
    }

    public function store(Request $request)
    {
        return new SocialMediaResource(SocialMedia::create($this->data($request)));
    }

    public function show(SocialMedia $social)
    {
        return new SocialMediaResource($social);
    }

    public function update(Request $request, SocialMedia $social)
    {
        $social->update($this->data($request));
        return new SocialMediaResource($social);
    }

    public function destroy(SocialMedia $social)
    {
        $social->delete();
        return response()->json(null, 204);
    }

    private function data(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url'  => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:60'],
        ]);
    }
}
