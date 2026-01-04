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
        ], 200);
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

            $cart->update([
                'quantity' => $newQuantity
            ]);
        } else {
            $cart = Cart::create([
                'user_id'    => $userId,
                'product_id' => $product->id,
                'quantity'   => $request->quantity,
            ]);
        }

        return response()->json([
            'message' => 'Product added to cart successfully',
            'data'    => $cart->load('product'),
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::ofUser($request->user()->id)
            ->with('product')
            ->findOrFail($id);

        if ($request->quantity > $cart->product->stock) {
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
        ], 200);
    }

    public function destroy(Request $request, $id)
    {
        $cart = Cart::ofUser($request->user()->id)->findOrFail($id);
        $cart->delete();

        return response()->json([
            'message' => 'Item removed from cart successfully',
        ], 200);
    }

    public function clear(Request $request)
    {
        Cart::ofUser($request->user()->id)->delete();

        return response()->json([
            'message' => 'Cart cleared successfully',
        ], 200);
    }
}
