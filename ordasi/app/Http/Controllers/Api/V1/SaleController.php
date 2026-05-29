<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SaleResource;
use App\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:sales.index')->only(['index']);
        $this->middleware('can:sales.show')->only(['show']);
        $this->middleware('can:sales.create')->only(['store']);
    }

    public function index()
    {
        return SaleResource::collection(
            Sale::with(['client', 'user'])->latest()->paginate(20)
        );
    }

    public function show(Sale $sale)
    {
        return new SaleResource($sale->load(['client', 'user', 'saleDetails.product']));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'client_id'            => ['required', 'exists:clients,id'],
            'tax'                  => ['required', 'numeric', 'min:0'],
            'total'                => ['required', 'numeric', 'min:0'],
            'details'              => ['required', 'array', 'min:1'],
            'details.*.product_id' => ['required', 'exists:products,id'],
            'details.*.quantity'   => ['required', 'integer', 'min:1'],
            'details.*.price'      => ['required', 'numeric', 'min:0'],
            'details.*.discount'   => ['sometimes', 'numeric', 'min:0'],
        ]);

        $sale = Sale::create([
            'client_id' => $data['client_id'],
            'tax'       => $data['tax'],
            'total'     => $data['total'],
            'user_id'   => $request->user()->id,
            'sale_date' => Carbon::now(),
        ]);

        $sale->saleDetails()->createMany($data['details']);

        return (new SaleResource($sale->load(['client', 'user', 'saleDetails.product'])))
            ->response()
            ->setStatusCode(201);
    }
}
