<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Order;
use App\ShoppingCart;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function __construct()
    {
        // Gestión admin
        $this->middleware('can:orders.index')->only(['index']);
        $this->middleware('can:orders.show')->only(['show']);
        $this->middleware('can:orders.edit')->only(['updateStatus']);
        // store / myOrders / myShow: cualquier usuario autenticado (cliente)
    }

    // ---------- Cliente ----------

    /** Checkout: crea la orden desde el carrito del usuario. */
    public function store(Request $request)
    {
        $data = $request->validate(['shipping_address' => ['nullable', 'string', 'max:255']]);

        $cart = ShoppingCart::with('details.product')->where('user_id', $request->user()->id)->first();
        if (! $cart || $cart->details->isEmpty()) {
            throw ValidationException::withMessages(['cart' => ['El carrito está vacío.']]);
        }
        foreach ($cart->details as $d) {
            if (! $d->product || $d->product->stock < $d->quantity) {
                throw ValidationException::withMessages(['stock' => ["Stock insuficiente para «{$d->product?->name}»."]]);
            }
        }

        $order = DB::transaction(function () use ($cart, $request, $data) {
            $total = $cart->details->sum(fn ($d) => $d->quantity * (float) $d->product->sell_price);
            $order = Order::create([
                'user_id'          => $request->user()->id,
                'order_date'       => Carbon::now(),
                'tax'              => 0,
                'total'            => round($total, 2),
                'shipping_address' => $data['shipping_address'] ?? null,
            ]);
            foreach ($cart->details as $d) {
                $order->details()->create([
                    'product_id' => $d->product_id,
                    'quantity'   => $d->quantity,
                    'price'      => $d->product->sell_price,
                ]);
                $d->product->decrement('stock', $d->quantity);
            }
            $cart->details()->delete();
            return $order;
        });

        return (new OrderResource($order->load('details.product', 'user')))->response()->setStatusCode(201);
    }

    public function myOrders(Request $request)
    {
        return OrderResource::collection(
            Order::with('details')->where('user_id', $request->user()->id)->latest()->paginate(20)
        );
    }

    public function myShow(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        return new OrderResource($order->load('details.product', 'user'));
    }

    // ---------- Admin ----------

    public function index(Request $request)
    {
        $query = Order::with('user');
        if ($s = $request->query('shipping_status')) {
            $query->where('shipping_status', $s);
        }
        $perPage = min((int) $request->query('per_page', 20) ?: 20, 1000);
        return OrderResource::collection($query->latest()->paginate($perPage));
    }

    public function show(Order $order)
    {
        return new OrderResource($order->load('details.product', 'user'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'shipping_status' => ['sometimes', 'in:PENDING,APPROVED,CANCELED,DELIVERED'],
            'payment_status'  => ['sometimes', 'in:PENDING,PAID,REFUNDED'],
        ]);
        $order->update($data);
        return new OrderResource($order->load('user'));
    }
}
