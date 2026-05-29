<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionResource;
use App\Subscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:subscriptions.index')->only(['index']);
        $this->middleware('can:subscriptions.destroy')->only(['destroy']);
    }

    public function index()
    {
        return SubscriptionResource::collection(Subscription::latest()->paginate(50));
    }

    /** Público: alta al newsletter desde el storefront. */
    public function store(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:255']]);
        $sub = Subscription::firstOrCreate(['email' => $data['email']]);
        return (new SubscriptionResource($sub))->response()->setStatusCode(201);
    }

    public function destroy(Subscription $subscription)
    {
        $subscription->delete();
        return response()->json(null, 204);
    }
}
