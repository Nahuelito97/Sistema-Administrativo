<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SaleResource;
use App\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:reports.day');
    }

    /**
     * Reporte de ventas. Sin parámetros = hoy. Con from/to = rango.
     */
    public function sales(Request $request)
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to'   => ['nullable', 'date'],
        ]);

        $query = Sale::with('client');

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('sale_date', [
                $request->from . ' 00:00:00',
                $request->to . ' 23:59:59',
            ]);
        } else {
            $query->whereDate('sale_date', Carbon::today());
        }

        $sales = $query->latest('sale_date')->get();

        return response()->json([
            'sales' => SaleResource::collection($sales),
            'total' => round($sales->sum(fn ($s) => (float) $s->total), 2),
            'count' => $sales->count(),
        ]);
    }
}
