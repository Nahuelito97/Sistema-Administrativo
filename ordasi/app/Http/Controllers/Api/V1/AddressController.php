<?php

namespace App\Http\Controllers\Api\V1;

use App\Address;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(['data' => $request->user()->addresses()->orderByDesc('is_default')->latest()->get()]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $this->ensureSingleDefault($request, $data);
        $address = $request->user()->addresses()->create($data);
        return response()->json(['data' => $address], 201);
    }

    public function update(Request $request, Address $address)
    {
        abort_unless($address->user_id === $request->user()->id, 403);
        $data = $this->validateData($request);
        $this->ensureSingleDefault($request, $data);
        $address->update($data);
        return response()->json(['data' => $address]);
    }

    public function destroy(Request $request, Address $address)
    {
        abort_unless($address->user_id === $request->user()->id, 403);
        $address->delete();
        return response()->json(null, 204);
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'label'       => ['nullable', 'string', 'max:60'],
            'recipient'   => ['required', 'string', 'max:255'],
            'phone'       => ['nullable', 'string', 'max:40'],
            'street'      => ['required', 'string', 'max:255'],
            'city'        => ['required', 'string', 'max:120'],
            'province'    => ['nullable', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'notes'       => ['nullable', 'string', 'max:255'],
            'is_default'  => ['boolean'],
        ]);
    }

    /** Si esta dirección es default, quita el default de las demás. */
    private function ensureSingleDefault(Request $request, array $data): void
    {
        if (! empty($data['is_default'])) {
            $request->user()->addresses()->update(['is_default' => false]);
        }
    }
}
