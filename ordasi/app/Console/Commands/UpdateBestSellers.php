<?php

namespace App\Console\Commands;

use App\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateBestSellers extends Command
{
    protected $signature = 'products:best-sellers';
    protected $description = 'Marca los productos más vendidos (top por unidades en los últimos 30 días)';

    public function handle(): int
    {
        // Reset
        Product::where('best_seller', true)->update(['best_seller' => false]);

        // Top por unidades vendidas en 30 días (órdenes no canceladas/reembolsadas).
        $top = DB::table('order_details')
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->where('orders.order_date', '>=', now()->subDays(30))
            ->where('orders.shipping_status', '!=', 'CANCELED')
            ->where('orders.payment_status', '!=', 'REFUNDED')
            ->groupBy('order_details.product_id')
            ->selectRaw('order_details.product_id, SUM(order_details.quantity) as sold')
            ->orderByDesc('sold');

        // Top 10% de los productos visibles (mínimo 8).
        $visible = Product::where('status', 'ACTIVE')->count();
        $limit = max(8, (int) ceil($visible * 0.10));

        $ids = $top->limit($limit)->pluck('product_id')->all();
        if ($ids) {
            Product::whereIn('id', $ids)->update(['best_seller' => true]);
        }

        $this->info(count($ids) . ' productos marcados como más vendidos.');
        return self::SUCCESS;
    }
}
