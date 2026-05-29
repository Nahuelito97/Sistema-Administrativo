<?php

namespace App\Http\Controllers\Api\V1;

use App\Client;
use App\Http\Controllers\Controller;
use App\Product;
use App\Sale;
use Carbon\Carbon;

class StatsController extends Controller
{
    public function index()
    {
        $todaySales = Sale::whereDate('sale_date', Carbon::today())->get();

        return response()->json([
            'products'          => Product::count(),
            'clients'           => Client::count(),
            'sales_today_count' => $todaySales->count(),
            'sales_today_total' => round($todaySales->sum(fn ($s) => (float) $s->total), 2),
            'low_stock'         => Product::where('stock', '<', 10)->count(),
        ]);
    }

    /** Totales de venta de los últimos 7 días (para el gráfico del dashboard). */
    public function salesDaily()
    {
        $days = collect(range(6, 0))->map(function ($i) {
            $date = Carbon::today()->subDays($i);
            $total = Sale::whereDate('sale_date', $date)->sum('total');
            return ['date' => $date->format('d/m'), 'total' => round((float) $total, 2)];
        });

        return response()->json($days->values());
    }
}
