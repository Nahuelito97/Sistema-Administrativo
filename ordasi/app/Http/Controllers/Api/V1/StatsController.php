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

    /** Ventas agrupadas por categoría (solo admin). */
    public function salesByCategory(Request $request)
    {
        abort_unless($request->user()->hasRole('Admin'), 403);

        $rows = OrderDetail::join('products', 'products.id', '=', 'order_details.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->select('categories.name', DB::raw('SUM(order_details.quantity) as qty'), DB::raw('SUM(order_details.quantity * order_details.price) as total'))
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($r) => ['name' => $r->name, 'qty' => (int) $r->qty, 'total' => round((float) $r->total, 2)]);

        return response()->json(['data' => $rows]);
    }

    /** Estadísticas de una tienda puntual (solo admin). */
    public function company(Request $request, Company $company)
    {
        abort_unless($request->user()->hasRole('Admin'), 403);

        $orders = Order::where('company_id', $company->id);
        $rep = $company->reputation();

        $daily = collect(range(29, 0))->map(function ($i) use ($company) {
            $date = Carbon::today()->subDays($i);
            $total = Order::where('company_id', $company->id)->whereDate('order_date', $date)->sum('total');
            return ['date' => $date->format('d/m'), 'total' => round((float) $total, 2)];
        });

        $topProducts = OrderDetail::join('orders', 'orders.id', '=', 'order_details.order_id')
            ->where('orders.company_id', $company->id)
            ->join('products', 'products.id', '=', 'order_details.product_id')
            ->select('products.name', DB::raw('SUM(order_details.quantity) as qty'))
            ->groupBy('products.id', 'products.name')->orderByDesc('qty')->limit(8)->get()
            ->map(fn ($r) => ['name' => $r->name, 'qty' => (int) $r->qty]);

        return response()->json([
            'company' => ['id' => $company->id, 'name' => $company->name],
            'kpis'    => [
                'orders'     => (clone $orders)->count(),
                'revenue'    => round((float) (clone $orders)->sum('total'), 2),
                'products'   => $company->products()->count(),
                'reputation' => $rep['avg'],
                'reviews'    => $rep['count'],
            ],
            'daily'        => $daily->values(),
            'top_products' => $topProducts,
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
