<?php

namespace App\Http\Controllers\Api\V1;

use App\Client;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreRequest;
use App\Http\Requests\Client\UpdateRequest;
use App\Http\Resources\ClientResource;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:clients.index')->only(['index', 'show']);
        $this->middleware('can:clients.create')->only(['store']);
        $this->middleware('can:clients.edit')->only(['update']);
        $this->middleware('can:clients.destroy')->only(['destroy']);
    }

    public function index()
    {
        return ClientResource::collection(Client::latest()->paginate(20));
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
