<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\RatingResource;
use App\Product;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function index(Product $product)
    {
        return RatingResource::collection(
            $product->ratings()->with('user')->latest()->paginate(20)
        );
    }

    /** Un usuario reseña un producto (una reseña por usuario; se actualiza). */
    public function store(Request $request, Product $product)
    {
        $data = $request->validate([
            'rating'  => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $rating = $product->ratings()->updateOrCreate(
            ['user_id' => $request->user()->id],
            ['rating' => $data['rating'], 'comment' => $data['comment'] ?? null],
        );

        return new RatingResource($rating->load('user'));
    }
}
