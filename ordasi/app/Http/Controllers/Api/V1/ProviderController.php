<?php

namespace App\Http\Controllers\Api\V1;

use App\Provider;
use App\Http\Controllers\Controller;
use App\Http\Requests\Provider\StoreRequest;
use App\Http\Requests\Provider\UpdateRequest;
use App\Http\Resources\ProviderResource;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:providers.index')->only(['index', 'show']);
        $this->middleware('can:providers.create')->only(['store']);
        $this->middleware('can:providers.edit')->only(['update']);
        $this->middleware('can:providers.destroy')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Provider::query();
        if ($s = $request->query('search')) {
            $query->where('name', 'like', "%{$s}%");
        }
        $perPage = min((int) $request->query('per_page', 20) ?: 20, 1000);
        return ProviderResource::collection($query->latest()->paginate($perPage));
    }

    public function store(StoreRequest $request)
    {
        return new ProviderResource(Provider::create($request->all()));
    }

    public function show(Provider $provider)
    {
        return new ProviderResource($provider);
    }

    public function update(UpdateRequest $request, Provider $provider)
    {
        $provider->update($request->all());
        return new ProviderResource($provider);
    }

    public function destroy(Provider $provider)
    {
        $provider->delete();
        return response()->json(null, 204);
    }
}
