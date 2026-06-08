<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;

/**
 * Wishlist del usuario autenticado (cualquier usuario logueado).
 */
class FavoriteController extends Controller
{
    /** Mis productos favoritos. */
    public function index(Request $request)
    {
        $favorites = $request->user()->favorites()
            ->with(['category', 'brand', 'company', 'promotions', 'images'])
            ->orderByDesc('favorites.created_at')
            ->paginate(min((int) $request->query('per_page', 20) ?: 20, 60));

        return ProductResource::collection($favorites);
    }

    /** Sólo los IDs (liviano, para hidratar el estado del storefront). */
    public function ids(Request $request)
    {
        return response()->json(['data' => $request->user()->favorites()->pluck('products.id')]);
    }

    /** Agrega/quita un producto de favoritos. */
    public function toggle(Request $request)
    {
        $data = $request->validate(['product_id' => ['required', 'exists:products,id']]);
        $user = $request->user();

        if ($user->favorites()->where('product_id', $data['product_id'])->exists()) {
            $user->favorites()->detach($data['product_id']);
            return response()->json(['favorited' => false]);
        }

        $user->favorites()->attach($data['product_id']);
        return response()->json(['favorited' => true]);
    }
}
