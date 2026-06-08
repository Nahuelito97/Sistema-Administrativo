<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OfferResource;
use App\Http\Resources\ProductResource;
use App\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OfferController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:offers.index')->only(['index', 'show']);
        $this->middleware('can:offers.create')->only(['store']);
        $this->middleware('can:offers.edit')->only(['update']);
        $this->middleware('can:offers.destroy')->only(['destroy']);
    }

    // ---------- Admin ----------

    public function index()
    {
        return OfferResource::collection(Offer::withCount('products')->latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['slug'] = Str::slug($data['name']) . '-' . Str::random(4);
        $offer = Offer::create($data);
        $offer->products()->sync($request->input('product_ids', []));
        return new OfferResource($offer->loadCount('products'));
    }

    public function show(Offer $offer)
    {
        return new OfferResource($offer->load('products')->loadCount('products'));
    }

    public function update(Request $request, Offer $offer)
    {
        $offer->update($this->validateData($request));
        $offer->products()->sync($request->input('product_ids', []));
        return new OfferResource($offer->loadCount('products'));
    }

    public function destroy(Offer $offer)
    {
        $offer->delete();
        return response()->json(null, 204);
    }

    // ---------- Público ----------

    public function publicIndex()
    {
        return OfferResource::collection(Offer::running()->withCount('products')->get());
    }

    public function publicShow(Offer $offer)
    {
        abort_unless($offer->isRunning(), 404);
        $products = $offer->products()
            ->whereIn('visibility', ['SHOP', 'BOTH'])->where('status', 'ACTIVE')
            ->with(['brand', 'company', 'promotions', 'images', 'offers'])->paginate(12);

        return (new OfferResource($offer))->additional([
            'items' => ProductResource::collection($products->items()),
            'meta'  => ['current_page' => $products->currentPage(), 'last_page' => $products->lastPage(), 'total' => $products->total()],
        ]);
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string', 'max:500'],
            'discount_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'starts_at'        => ['required', 'date'],
            'ends_at'          => ['required', 'date', 'after:starts_at'],
            'is_active'        => ['boolean'],
            'product_ids'      => ['array'],
            'product_ids.*'    => ['exists:products,id'],
        ]);
    }
}
