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
}
