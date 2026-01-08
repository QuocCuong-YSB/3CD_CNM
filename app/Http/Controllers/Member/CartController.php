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
            ->with('product')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Cart retrieved successfully',
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

        if ($request->quantity > $product->quantity) {
            return response()->json([
                'message' => 'Out of stock!'
            ], 400);
        }

        $cart = Cart::where('user_id', $userId)
            ->where('product_id', $product->id)
            ->first();

        if ($cart) {
            $cart->quantity += $request->quantity; 
        } else {
            $cart = new Cart([
                'user_id' => $userId,
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'price' => $product->price
            ]);
        }

        $cart->save();

        $product->quantity -= $request->quantity;
        $product->save();

        $cart->load('product'); 

        return response()->json([
            'status' => true,
            'message' => 'Product added to cart successfully',
            'data' => $cart
        ], 200);
    }

    public function update(Request $request, $product_id)
    {
        $request->validate([
            'quantity' => 'required|integer',
        ]);

        $userId = $request->user()->id;
        $product = Product::findOrFail($product_id);

        $cart = Cart::where('user_id', $userId)
            ->where('product_id', $product_id)
            ->firstOrFail();

        $changeQuantity = $request->quantity; 

        if ($changeQuantity > 0) {

            if ($changeQuantity > $product->quantity) {
                return response()->json([
                    'message' => 'Not enough stock to increase quantity'
                ], 400);
            }

            $cart->quantity += $changeQuantity;
            $product->quantity -= $changeQuantity; 
        } else

        if ($changeQuantity < 0) {
            $decreaseAmount = abs($changeQuantity);
            if ($decreaseAmount > $cart->quantity) {
                return response()->json([
                    'message' => 'Cannot reduce quantity below 0'
                ], 400);
            }

            $cart->quantity -= $decreaseAmount;
            $product->quantity += $decreaseAmount; 
        } else {
            return response()->json([
                'message' => 'No change in quantity'
            ], 400);
        }

        $cart->save();
        $product->save();
        $cart->load('product');

        return response()->json([
            'status' => true,
            'message' => 'Cart updated successfully',
            'data' => $cart
        ], 200);
    }

    public function destroy(Request $request, $product_id)
    {
        $cart = Cart::where('user_id', $request->user()->id)
            ->where('product_id', $product_id)
            ->firstOrFail();

        $cart->delete();

        return response()->json([
            'status' => true,
            'message' => 'Product removed from cart'
        ], 200);
    }

    public function clear(Request $request)
    {
        Cart::where('user_id', $request->user()->id)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Cart cleared successfully'
        ], 200);
    }
}
