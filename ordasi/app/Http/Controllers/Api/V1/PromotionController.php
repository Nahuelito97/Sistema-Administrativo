<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PromotionResource;
use App\Promotion;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:promotions.index')->only(['index', 'show']);
        $this->middleware('can:promotions.create')->only(['store']);
        $this->middleware('can:promotions.edit')->only(['update']);
        $this->middleware('can:promotions.destroy')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Promotion::withCount('products');
        if ($s = $request->query('search')) {
            $query->where('name', 'like', "%{$s}%");
        }
        $perPage = min((int) $request->query('per_page', 20) ?: 20, 1000);
        return PromotionResource::collection($query->latest()->paginate($perPage));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $promotion = Promotion::create($data);
        $promotion->products()->sync($request->input('product_ids', []));
        return new PromotionResource($promotion->loadCount('products'));
    }

    public function show(Promotion $promotion)
    {
        return new PromotionResource($promotion->load('products')->loadCount('products'));
    }

    public function update(Request $request, Promotion $promotion)
    {
        $data = $this->validateData($request);
        $promotion->update($data);
        $promotion->products()->sync($request->input('product_ids', []));
        return new PromotionResource($promotion->loadCount('products'));
    }

    public function destroy(Promotion $promotion)
    {
        $promotion->delete();
        return response()->json(null, 204);
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'promotion_type'        => ['required', 'in:percent,fixed_amount,combo,wholesale'],
            'start_date'            => ['required', 'date'],
            'ending_date'           => ['required', 'date', 'after_or_equal:start_date'],
            'discount_rate'         => ['nullable', 'required_if:promotion_type,percent', 'numeric', 'min:0', 'max:100'],
            'fixed_amount_discount' => ['nullable', 'required_if:promotion_type,fixed_amount', 'numeric', 'min:0'],
            'combo_buy'             => ['nullable', 'required_if:promotion_type,combo', 'integer', 'min:2'],
            'combo_pay'             => ['nullable', 'required_if:promotion_type,combo', 'integer', 'min:1', 'lt:combo_buy'],
            'wholesale_min_qty'     => ['nullable', 'required_if:promotion_type,wholesale', 'integer', 'min:2'],
            'wholesale_price'       => ['nullable', 'required_if:promotion_type,wholesale', 'numeric', 'min:0'],
            'product_ids'           => ['array'],
            'product_ids.*'         => ['exists:products,id'],
        ]);
    }
}
