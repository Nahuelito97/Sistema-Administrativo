<?php

namespace App\Http\Controllers\Api\V1;

use App\Business;
use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessResource;
use Illuminate\Http\Request;

class BusinessController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:business.index')->only(['show']);
        $this->middleware('can:business.edit')->only(['update']);
    }

    public function show()
    {
        return new BusinessResource(Business::firstOrFail());
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'mail'        => ['nullable', 'email', 'max:255'],
            'address'     => ['nullable', 'string', 'max:255'],
            'ruc'         => ['nullable', 'string', 'max:20'],
        ]);

        $business = Business::firstOrFail();
        $business->update($data);

        return new BusinessResource($business);
    }
}
