<?php

namespace App\Http\Controllers\Api\V1;

use App\Currency;
use App\Http\Controllers\Controller;
use App\Http\Resources\CurrencyResource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CurrencyController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:currencies.index')->only(['index', 'show']);
        $this->middleware('can:currencies.create')->only(['store']);
        $this->middleware('can:currencies.edit')->only(['update']);
        $this->middleware('can:currencies.destroy')->only(['destroy']);
    }

    public function index()
    {
        return CurrencyResource::collection(Currency::latest()->paginate(50));
    }

    public function store(Request $request)
    {
        return new CurrencyResource(Currency::create($this->data($request)));
    }

    public function show(Currency $currency)
    {
        return new CurrencyResource($currency);
    }

    public function update(Request $request, Currency $currency)
    {
        $currency->update($this->data($request, $currency->id));
        return new CurrencyResource($currency);
    }

    public function destroy(Currency $currency)
    {
        $currency->delete();
        return response()->json(null, 204);
    }

    private function data(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'iso'    => ['required', 'string', 'size:3', Rule::unique('currencies', 'iso')->ignore($ignoreId)],
            'name'   => ['required', 'string', 'max:255'],
            'symbol' => ['nullable', 'string', 'max:8'],
        ]);
    }
}
