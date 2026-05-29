<?php

namespace App\Http\Controllers\Api\V1;

use App\Business;
use App\Http\Controllers\Controller;
use App\Http\Resources\PurchaseResource;
use App\Purchase;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:purchases.index')->only(['index']);
        $this->middleware('can:purchases.show')->only(['show']);
        $this->middleware('can:purchases.create')->only(['store']);
        $this->middleware('can:change.status.purchases')->only(['changeStatus']);
        $this->middleware('can:purchases.pdf')->only(['pdf']);
        $this->middleware('can:upload.purchases')->only(['uploadComprobante']);
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

    /** Alterna VALID / CANCELED. */
    public function changeStatus(Purchase $purchase)
    {
        $purchase->update(['status' => $purchase->status === 'VALID' ? 'CANCELED' : 'VALID']);
        return new PurchaseResource($purchase->load(['provider', 'user']));
    }

    /** PDF del comprobante de compra. */
    public function pdf(Purchase $purchase)
    {
        $purchaseDetails = $purchase->purchaseDetails()->with('product')->get();
        $subtotal = $purchaseDetails->sum(fn ($d) => $d->quantity * $d->price);
        $company = Business::first();
        $pdf = PDF::loadView('admin.purchase.pdf', compact('purchase', 'subtotal', 'purchaseDetails', 'company'));
        return $pdf->download('Reporte_de_compra_' . $purchase->id . '.pdf');
    }

    /** Sube el comprobante (imagen) de la compra. */
    public function uploadComprobante(Request $request, Purchase $purchase)
    {
        $request->validate([
            'comprobante' => ['required', 'image', 'max:4096'],
        ]);

        $path = $request->file('comprobante')->store('comprobantes', 'public');
        $purchase->update(['picture' => $path]);

        return new PurchaseResource($purchase->load(['provider', 'user']));
    }
}
