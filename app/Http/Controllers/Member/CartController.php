<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $carts = Cart::ofUser($request->user()->id)
            ->with('product')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $carts
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $userId = $request->user()->id;
        $product = Product::findOrFail($request->product_id);

        if ($request->quantity > $product->stock) {
            return response()->json([
                'message' => 'Out of stock!',
            ], 400);
        }

        $cart = Cart::where('user_id', $userId)
            ->where('product_id', $product->id)
            ->first();

        if ($cart) {
            $newQuantity = $cart->quantity + $request->quantity;
            if ($newQuantity > $product->stock) {
                return response()->json([
                    'message' => 'Out of stock!',
                ], 400);
            }
            $cart->increment('quantity', $request->quantity);
        } else {
            $cart = Cart::create([
                'user_id' => $userId,
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'price' => $product->price,
            ]);
        }

        return response()->json([
            'message' => 'Product added to cart',
            'data'    => $cart,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::ofUser($request->user()->id)->findOrFail($id);
        $product = $cart->product;

        if ($request->quantity > $product->stock) {
            return response()->json([
                'message' => 'Out of stock!',
            ], 400);
        }

        $cart->update([
            'quantity' => $request->quantity,
        ]);

        return response()->json([
            'message' => 'Cart updated successfully',
            'data'    => $cart,
        ]);
    }


    public function destroy(Request $request, $id)
    {
        $cart = Cart::ofUser($request->user()->id)->findOrFail($id);
        $cart->delete();

        return response()->json([
            'message' => 'Item removed from cart',
        ]);
    }

    public function clear(Request $request)
    {
        Cart::ofUser($request->user()->id)->delete();

        return response()->json([
            'message' => 'Cart cleared successfully',
        ]);
    }
}
