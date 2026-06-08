<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Product;
use App\ProductVariant;
use App\ShoppingCart;
use App\ShoppingCartDetail;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    private function cart(Request $request): ShoppingCart
    {
        return ShoppingCart::firstOrCreate(['user_id' => $request->user()->id]);
    }

    private function withItems(ShoppingCart $cart): CartResource
    {
        return new CartResource($cart->load('details.product.brand', 'details.variant'));
    }

    public function show(Request $request)
    {
        return $this->withItems($this->cart($request));
    }

    public function addItem(Request $request)
    {
        $data = $request->validate([
            'product_id'         => ['required', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'quantity'           => ['sometimes', 'integer', 'min:1'],
        ]);

        $product = Product::findOrFail($data['product_id']);
        $variantId = $data['product_variant_id'] ?? null;

        // No podés comprar productos de tu propia tienda.
        if ($product->company_id && $product->company_id === $request->user()->company_id) {
            throw ValidationException::withMessages(['product_id' => ['No podés comprar productos de tu propia tienda.']]);
        }

        // Si el producto tiene variantes, hay que elegir una; si no, no se acepta variante.
        if ($product->has_variants && ! $variantId) {
            throw ValidationException::withMessages(['product_variant_id' => ['Elegí una variante.']]);
        }
        if ($variantId) {
            $variant = ProductVariant::findOrFail($variantId);
            if ($variant->product_id !== $product->id) {
                throw ValidationException::withMessages(['product_variant_id' => ['La variante no pertenece al producto.']]);
            }
        }

        $cart = $this->cart($request);
        $qty = $data['quantity'] ?? 1;
        $detail = $cart->details()
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variantId)
            ->first();

        if ($detail) {
            $detail->increment('quantity', $qty);
        } else {
            $cart->details()->create([
                'product_id'         => $product->id,
                'product_variant_id' => $variantId,
                'quantity'           => $qty,
            ]);
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
