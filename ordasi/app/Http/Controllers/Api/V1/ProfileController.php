<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProfileResource;
use App\Profile;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $profile = Profile::firstOrCreate(['user_id' => $request->user()->id]);
        return new ProfileResource($profile);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'dni'     => ['nullable', 'string', 'max:30'],
            'ruc'     => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone'   => ['nullable', 'string', 'max:30'],
        ]);

        $profile = Profile::updateOrCreate(['user_id' => $request->user()->id], $data);
        return new ProfileResource($profile);
    }
}
