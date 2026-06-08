<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { color: #1e2126; font-size: 12px; }
        h1 { font-size: 20px; margin: 0; }
        .muted { color: #5c6574; }
        .head { border-bottom: 2px solid #fbc401; padding-bottom: 10px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th { text-align: left; background: #f8f9fc; padding: 8px; font-size: 11px; text-transform: uppercase; color: #5c6574; }
        td { padding: 8px; border-bottom: 1px solid #e4e9ed; }
        .right { text-align: right; }
        .total { font-size: 16px; font-weight: bold; }
        .box { background: #f8f9fc; padding: 10px 12px; border-radius: 6px; margin-top: 12px; }
    </style>
</head>
<body>
    <div class="head">
        <h1>Comprobante de compra</h1>
        <p class="muted">Pedido #{{ $order->id }} · {{ optional($order->order_date)->format('d/m/Y H:i') }}</p>
    </div>

    <table style="margin-top:0">
        <tr>
            <td style="border:0; width:50%; vertical-align:top">
                <strong>Tienda</strong><br>
                {{ $order->company->name ?? '—' }}
            </td>
            <td style="border:0; width:50%; vertical-align:top">
                <strong>Cliente</strong><br>
                {{ $order->user->name ?? '—' }}<br>
                <span class="muted">{{ $order->user->email ?? '' }}</span>
            </td>
        </tr>
    </table>

    <div class="box">
        <strong>Envío:</strong> {{ $order->shipping_address ?? 'A coordinar' }}
        @if($order->tracking_code) · <strong>Seguimiento:</strong> {{ $order->shipping_carrier }} {{ $order->tracking_code }} @endif
    </div>

    <table>
        <thead>
            <tr><th>Producto</th><th>Cant.</th><th class="right">Precio</th><th class="right">Subtotal</th></tr>
        </thead>
        <tbody>
            @foreach($order->details as $d)
                <tr>
                    <td>{{ $d->product->name ?? '#'.$d->product_id }}@if($d->variant_name) <span class="muted">({{ $d->variant_name }})</span>@endif</td>
                    <td>{{ $d->quantity }}</td>
                    <td class="right">$ {{ number_format($d->price, 2) }}</td>
                    <td class="right">$ {{ number_format($d->price * $d->quantity, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr><td colspan="3" class="right total">Total</td><td class="right total">$ {{ number_format($order->total, 2) }}</td></tr>
        </tfoot>
    </table>

    <p class="muted" style="margin-top:24px; font-size:10px">Comprobante generado por Ordasi. No válido como factura fiscal.</p>
</body>
</html>
