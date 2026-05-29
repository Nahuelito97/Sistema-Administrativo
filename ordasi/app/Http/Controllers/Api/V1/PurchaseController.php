<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PurchaseResource;
use App\Purchase;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:purchases.index')->only(['index']);
        $this->middleware('can:purchases.show')->only(['show']);
        $this->middleware('can:purchases.create')->only(['store']);
    }

    public function index()
    {
        return PurchaseResource::collection(
            Purchase::with(['provider', 'user'])->latest()->paginate(20)
        );
    }

    public function show(Purchase $purchase)
    {
        return new PurchaseResource($purchase->load(['provider', 'user', 'purchaseDetails.product']));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'provider_id'          => ['required', 'exists:providers,id'],
            'tax'                  => ['required', 'numeric', 'min:0'],
            'total'                => ['required', 'numeric', 'min:0'],
            'details'              => ['required', 'array', 'min:1'],
            'details.*.product_id' => ['required', 'exists:products,id'],
            'details.*.quantity'   => ['required', 'integer', 'min:1'],
            'details.*.price'      => ['required', 'numeric', 'min:0'],
        ]);

        $purchase = Purchase::create([
            'provider_id'   => $data['provider_id'],
            'tax'           => $data['tax'],
            'total'         => $data['total'],
            'user_id'       => $request->user()->id,
            'purchase_date' => Carbon::now(),
        ]);

        $purchase->purchaseDetails()->createMany($data['details']);

        return (new PurchaseResource($purchase->load(['provider', 'user', 'purchaseDetails.product'])))
            ->response()
            ->setStatusCode(201);
    }
}
