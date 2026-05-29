<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Order;
use App\Services\MercadoPagoService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /** Crea la preferencia de pago para una orden (dueño de la orden). */
    public function pay(Request $request, Order $order, MercadoPagoService $mp)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        if (! $mp->configured()) {
            return response()->json(['message' => 'MercadoPago no está configurado (falta MERCADOPAGO_ACCESS_TOKEN).'], 503);
        }

        $order->loadMissing('details.product');
        $pref = $mp->createPreference($order);
        $order->update(['payment_platform' => 'mercadopago', 'preference_id' => $pref['preference_id']]);

        return response()->json($pref);
    }

    /** Webhook público: MercadoPago notifica el estado del pago. */
    public function webhook(Request $request, MercadoPagoService $mp)
    {
        $type = $request->input('type') ?? $request->query('type');
        $paymentId = $request->input('data.id') ?? $request->query('data_id') ?? $request->query('id');

        if ($type === 'payment' && $paymentId && $mp->configured()) {
            $payment = $mp->getPayment((string) $paymentId);
            if ($payment && $payment['external_reference'] && ($order = Order::find($payment['external_reference']))) {
                $map = [
                    'approved'   => 'PAID',
                    'refunded'   => 'REFUNDED',
                    'pending'    => 'PENDING',
                    'in_process' => 'PENDING',
                    'rejected'   => 'PENDING',
                    'cancelled'  => 'PENDING',
                ];
                $order->update([
                    'payment_status'  => $map[$payment['status']] ?? 'PENDING',
                    'payment_id'      => (string) $paymentId,
                    'shipping_status' => $payment['status'] === 'approved' && $order->shipping_status === 'PENDING'
                        ? 'APPROVED' : $order->shipping_status,
                ]);
            }
        }

        return response()->json(['received' => true]);
    }
}
