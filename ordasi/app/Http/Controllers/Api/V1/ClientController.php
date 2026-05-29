<?php

namespace App\Http\Controllers\Api\V1;

use App\Client;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreRequest;
use App\Http\Requests\Client\UpdateRequest;
use App\Http\Resources\ClientResource;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:clients.index')->only(['index', 'show']);
        $this->middleware('can:clients.create')->only(['store']);
        $this->middleware('can:clients.edit')->only(['update']);
        $this->middleware('can:clients.destroy')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Client::query();
        if ($s = $request->query('search')) {
            $query->where('name', 'like', "%{$s}%");
        }
        $perPage = min((int) $request->query('per_page', 20) ?: 20, 1000);
        return ClientResource::collection($query->latest()->paginate($perPage));
    }

    public function store(StoreRequest $request)
    {
        return new ClientResource(Client::create($request->all()));
    }

    public function show(Client $client)
    {
        return new ClientResource($client);
    }

    public function update(UpdateRequest $request, Client $client)
    {
        $client->update($request->all());
        return new ClientResource($client);
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return response()->json(null, 204);
    }
}
