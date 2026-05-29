<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SliderResource;
use App\Slider;
use Illuminate\Http\Request;

class SliderController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:sliders.index')->only(['index', 'show']);
        $this->middleware('can:sliders.create')->only(['store', 'uploadImage']);
        $this->middleware('can:sliders.edit')->only(['update', 'uploadImage']);
        $this->middleware('can:sliders.destroy')->only(['destroy']);
    }

    public function index()
    {
        return SliderResource::collection(Slider::latest()->paginate(50));
    }

    /** Público: sliders activos para el home del storefront. */
    public function publicIndex()
    {
        return SliderResource::collection(Slider::where('active', true)->latest()->get());
    }

    public function store(Request $request)
    {
        return new SliderResource(Slider::create($this->data($request)));
    }

    public function show(Slider $slider)
    {
        return new SliderResource($slider);
    }

    public function update(Request $request, Slider $slider)
    {
        $slider->update($this->data($request));
        return new SliderResource($slider);
    }

    public function destroy(Slider $slider)
    {
        $slider->delete();
        return response()->json(null, 204);
    }

    public function uploadImage(Request $request, Slider $slider)
    {
        $request->validate(['image' => ['required', 'image', 'max:4096']]);
        $slider->update(['image' => $request->file('image')->store('sliders', 'public')]);
        return new SliderResource($slider);
    }

    private function data(Request $request): array
    {
        return $request->validate([
            'title'  => ['nullable', 'string', 'max:255'],
            'body'   => ['nullable', 'string'],
            'link'   => ['nullable', 'string', 'max:255'],
            'active' => ['boolean'],
        ]);
    }
}
