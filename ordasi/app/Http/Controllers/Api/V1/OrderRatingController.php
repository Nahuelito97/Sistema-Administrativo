<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Notification;
use App\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderRatingController extends Controller
{
    /** El comprador califica una orden entregada (producto/atención/envío). */
    public function store(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        if ($order->shipping_status !== 'DELIVERED') {
            throw ValidationException::withMessages(['order' => ['Solo podés calificar pedidos entregados.']]);
        }
        if ($order->rating()->exists()) {
            throw ValidationException::withMessages(['order' => ['Ya calificaste este pedido.']]);
        }

        $data = $request->validate([
            'product_score'   => ['required', 'integer', 'min:1', 'max:5'],
            'attention_score' => ['required', 'integer', 'min:1', 'max:5'],
            'shipping_score'  => ['required', 'integer', 'min:1', 'max:5'],
            'comment'         => ['nullable', 'string', 'max:1000'],
        ]);

        $rating = $order->rating()->create($data + [
            'user_id'    => $request->user()->id,
            'company_id' => $order->company_id,
        ]);

        Notification::notifyCompany($order->company_id, 'rating', 'Nueva calificación', "Pedido #{$order->id}: " . $rating->average . '★', '/orders');

        return response()->json(['data' => $rating], 201);
    }
}
