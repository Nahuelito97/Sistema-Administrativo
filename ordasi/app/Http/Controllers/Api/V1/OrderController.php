<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\ScopesToSeller;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Order;
use App\ShoppingCart;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    use ScopesToSeller;

    public function __construct()
    {
        // Gestión admin / vendedor (scoped)
        $this->middleware('can:orders.index')->only(['index']);
        $this->middleware('can:orders.show')->only(['show']);
        $this->middleware('can:orders.edit')->only(['updateStatus']);
        // store / myOrders / myShow: cualquier usuario autenticado (cliente)
    }

    // ---------- Cliente ----------

    /**
     * Checkout: arma las órdenes desde el carrito.
     * Una compra con productos de N tiendas genera N órdenes (una por vendedor).
     */
    public function store(Request $request)
    {
        $data = $request->validate(['shipping_address' => ['nullable', 'string', 'max:255']]);

        $cart = ShoppingCart::with('details.product', 'details.variant')->where('user_id', $request->user()->id)->first();
        if (! $cart || $cart->details->isEmpty()) {
            throw ValidationException::withMessages(['cart' => ['El carrito está vacío.']]);
        }
        foreach ($cart->details as $d) {
            if (! $d->product) {
                throw ValidationException::withMessages(['stock' => ['Un producto del carrito ya no existe.']]);
            }
            // El stock disponible sale de la variante si la hay, si no del producto.
            $available = $d->variant ? $d->variant->stock : $d->product->stock;
            if ($available < $d->quantity) {
                $label = $d->product->name . ($d->variant ? " ({$d->variant->name})" : '');
                throw ValidationException::withMessages(['stock' => ["Stock insuficiente para «{$label}»."]]);
            }
        }

        $orders = DB::transaction(function () use ($cart, $request, $data) {
            // Agrupar por tienda → una orden por company.
            $groups = $cart->details->groupBy(fn ($d) => $d->product->company_id);
            $created = collect();

            foreach ($groups as $companyId => $details) {
                $unitPrice = fn ($d) => (float) ($d->variant->price ?? $d->product->sell_price);
                $total = $details->sum(fn ($d) => $d->quantity * $unitPrice($d));
                $order = Order::create([
                    'user_id'          => $request->user()->id,
                    'company_id'       => $companyId,
                    'seller_id'        => $this->sellerForCompany($companyId),
                    'order_date'       => Carbon::now(),
                    'tax'              => 0,
                    'total'            => round($total, 2),
                    'shipping_address' => $data['shipping_address'] ?? null,
                ]);
                foreach ($details as $d) {
                    $order->details()->create([
                        'product_id'         => $d->product_id,
                        'product_variant_id' => $d->product_variant_id,
                        'variant_name'       => $d->variant?->name,
                        'quantity'           => $d->quantity,
                        'price'              => $unitPrice($d),
                    ]);
                    // Descontar stock de la variante o del producto.
                    $d->variant ? $d->variant->decrement('stock', $d->quantity) : $d->product->decrement('stock', $d->quantity);
                }
                $created->push($order);
            }

            $cart->details()->delete();
            return $created;
        });

        return OrderResource::collection(
            Order::with('details.product', 'user', 'company')->whereIn('id', $orders->pluck('id'))->latest()->get()
        )->response()->setStatusCode(201);
    }

    /** Vendedor principal de una tienda (para referencia en la orden). */
    private function sellerForCompany(?int $companyId): ?int
    {
        if (! $companyId) {
            return null;
        }
        return User::where('company_id', $companyId)
            ->where('status_seller_id', User::SELLER_ACTIVE)
            ->orderBy('id')
            ->value('id');
    }

    public function myOrders(Request $request)
    {
        return OrderResource::collection(
            Order::with(['details', 'company'])->where('user_id', $request->user()->id)->latest()->paginate(20)
        );
    }

    public function myShow(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        return new OrderResource($order->load('details.product', 'user', 'company'));
    }

    // ---------- Admin / Vendedor (scoped) ----------

    public function index(Request $request)
    {
        $query = $this->scopeOwned(Order::with(['user', 'company']), $request);
        if ($s = $request->query('shipping_status')) {
            $query->where('shipping_status', $s);
        }
        $perPage = min((int) $request->query('per_page', 20) ?: 20, 1000);
        return OrderResource::collection($query->latest()->paginate($perPage));
    }

    public function show(Request $request, Order $order)
    {
        $this->assertOwned($order, $request);
        return new OrderResource($order->load('details.product', 'user', 'company'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $this->assertOwned($order, $request);
        $data = $request->validate([
            'shipping_status' => ['sometimes', 'in:PENDING,APPROVED,CANCELED,DELIVERED'],
            'payment_status'  => ['sometimes', 'in:PENDING,PAID,REFUNDED'],
        ]);
        $order->update($data);
        return new OrderResource($order->load('user', 'company'));
    }
}
