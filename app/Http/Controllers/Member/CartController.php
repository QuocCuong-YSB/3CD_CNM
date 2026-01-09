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
        $carts = Cart::where('user_id', $request->user()->id)
            ->with('product:id,name,price,quantity,sale,image')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $carts
        ]);
    }

    public function count(Request $request)
    {
        $count = Cart::where('user_id', $request->user()->id)
            ->sum('quantity');

        return response()->json([
            'count' => $count
        ]);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $userId  = $request->user()->id;
        $product = Product::findOrFail($request->product_id);

        $cart = Cart::firstOrNew([
            'user_id'    => $userId,
            'product_id' => $product->id,
        ]);

        $newQuantity = $cart->exists
            ? $cart->quantity + $request->quantity
            : $request->quantity;

        if ($newQuantity > $product->quantity) {
            return response()->json([
                'message' => 'Not enough stock'
            ], 400);
        }

        $cart->quantity = $newQuantity;
        $cart->save();

        $cart->load('product');

        return response()->json([
            'status'  => true,
            'message' => 'Added to cart',
            'data'    => $cart
        ]);
    }

    public function update(Request $request, $product_id)
    {
        $request->validate([
            'quantity' => 'required|integer',
        ]);

        $userId = $request->user()->id;

        $cart = Cart::where('user_id', $userId)
            ->where('product_id', $product_id)
            ->firstOrFail();

        $product = Product::findOrFail($product_id);

        $newQuantity = $cart->quantity + $request->quantity;

        if ($newQuantity < 1) {
            return response()->json([
                'message' => 'Quantity must be at least 1'
            ], 400);
        }

        if ($newQuantity > $product->quantity) {
            return response()->json([
                'message' => 'Not enough stock'
            ], 400);
        }

        $cart->quantity = $newQuantity;
        $cart->save();

        $cart->load('product');

        return response()->json([
            'status'  => true,
            'message' => 'Cart updated',
            'data'    => $cart
        ]);
    }

    public function destroy(Request $request, $product_id)
    {
        Cart::where('user_id', $request->user()->id)
            ->where('product_id', $product_id)
            ->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Removed from cart'
        ]);
    }

    public function clear(Request $request)
    {
        Cart::where('user_id', $request->user()->id)->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Cart cleared'
        ]);
    }
}
