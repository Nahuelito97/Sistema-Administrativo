<?php

namespace App\Http\Controllers\Api\V1;

use App\Client;
use App\Company;
use App\Http\Controllers\Controller;
use App\Order;
use App\OrderDetail;
use App\Product;
use App\Sale;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    /** Estadísticas del marketplace (solo admin). */
    public function marketplace(Request $request)
    {
        abort_unless($request->user()->hasRole('Admin'), 403);

        $topCompanies = Order::select('company_id', DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as orders'))
            ->with('company:id,name')
            ->groupBy('company_id')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->map(fn ($r) => ['name' => $r->company?->name ?? '—', 'total' => round((float) $r->total, 2), 'orders' => (int) $r->orders]);

        $topProducts = OrderDetail::select('product_id', DB::raw('SUM(quantity) as qty'))
            ->with('product:id,name')
            ->groupBy('product_id')
            ->orderByDesc('qty')
            ->limit(8)
            ->get()
            ->map(fn ($r) => ['name' => $r->product?->name ?? '—', 'qty' => (int) $r->qty]);

        $byStatus = Order::select('shipping_status', DB::raw('COUNT(*) as count'))
            ->groupBy('shipping_status')
            ->pluck('count', 'shipping_status');

        return response()->json([
            'kpis' => [
                'companies' => Company::where('status', 'active')->count(),
                'sellers'   => User::where('status_seller_id', User::SELLER_ACTIVE)->count(),
                'orders'    => Order::count(),
                'gmv'       => round((float) Order::sum('total'), 2),
            ],
            'top_companies'    => $topCompanies,
            'top_products'     => $topProducts,
            'orders_by_status' => $byStatus,
        ]);
    }

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
