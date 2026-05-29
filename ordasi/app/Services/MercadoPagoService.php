<?php

namespace App\Services;

use App\Order;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

class MercadoPagoService
{
    public function __construct()
    {
        MercadoPagoConfig::setAccessToken((string) config('services.mercadopago.access_token'));
    }

    public function configured(): bool
    {
        return ! empty(config('services.mercadopago.access_token'));
    }

    /** Crea una preferencia de Checkout Pro para la orden. */
    public function createPreference(Order $order): array
    {
        $front = rtrim((string) config('services.mercadopago.front_url'), '/');

        $items = $order->details->map(fn ($d) => [
            'title'       => $d->product->name ?? 'Producto',
            'quantity'    => (int) $d->quantity,
            'unit_price'  => (float) $d->price,
            'currency_id' => 'ARS',
        ])->values()->all();

        $preference = (new PreferenceClient())->create([
            'items'              => $items,
            'external_reference' => (string) $order->id,
            'back_urls'          => [
                'success' => "{$front}/checkout/success?order={$order->id}",
                'pending' => "{$front}/checkout/pending?order={$order->id}",
                'failure' => "{$front}/checkout/failure?order={$order->id}",
            ],
            'auto_return'      => 'approved',
            'notification_url' => url('/api/v1/webhooks/mercadopago'),
        ]);

        return [
            'preference_id' => $preference->id,
            'init_point'    => $preference->init_point,
        ];
    }

    /** Consulta un pago por id. Devuelve [status, external_reference] o null. */
    public function getPayment(string $paymentId): ?array
    {
        try {
            $payment = (new PaymentClient())->get((int) $paymentId);
            return [
                'status'             => $payment->status,
                'external_reference' => $payment->external_reference,
            ];
        } catch (MPApiException) {
            return null;
        }
    }
}
