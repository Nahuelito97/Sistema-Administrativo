<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\ShoppingCart;
use App\ShoppingCartDetail;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private function cart(Request $request): ShoppingCart
    {
        return ShoppingCart::firstOrCreate(['user_id' => $request->user()->id]);
    }

    private function withItems(ShoppingCart $cart): CartResource
    {
        return new CartResource($cart->load('details.product.brand'));
    }

    public function show(Request $request)
    {
        return $this->withItems($this->cart($request));
    }

    public function addItem(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity'   => ['sometimes', 'integer', 'min:1'],
        ]);
        $cart = $this->cart($request);
        $qty = $data['quantity'] ?? 1;
        $detail = $cart->details()->where('product_id', $data['product_id'])->first();
        if ($detail) {
            $detail->increment('quantity', $qty);
        } else {
            $cart->details()->create(['product_id' => $data['product_id'], 'quantity' => $qty]);
        }
        return $this->withItems($cart);
    }

    public function updateItem(Request $request, ShoppingCartDetail $item)
    {
        $cart = $this->cart($request);
        abort_unless($item->shopping_cart_id === $cart->id, 404);
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1']]);
        $item->update($data);
        return $this->withItems($cart);
    }

    public function removeItem(Request $request, ShoppingCartDetail $item)
    {
        $cart = $this->cart($request);
        abort_unless($item->shopping_cart_id === $cart->id, 404);
        $item->delete();
        return $this->withItems($cart);
    }

    public function clear(Request $request)
    {
        $cart = $this->cart($request);
        $cart->details()->delete();
        return $this->withItems($cart);
    }
}
