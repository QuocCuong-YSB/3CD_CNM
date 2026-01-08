<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\History;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CheckoutController extends Controller
{
    public function placeOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user' => 'required|array',
            'user.id' => 'required',
            'cart' => 'required|array',
            'paymentMethod' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user = $request->user;
        $cart = $request->cart;
        $voucherCode = $request->voucherCode;
        $orderCode = History::generateOrderCode();

        DB::beginTransaction();

        try {
            $discountMultiplier = 1;
            if ($voucherCode === 'NEWUSER') {
                $hasOrders = History::where('id_user', $user['id'])->where('status', '!=', 3)->exists();
                if (!$hasOrders) {
                    $discountMultiplier = 0.95;
                }
            }

            foreach ($cart as $id => $quantity) {
                $product = Product::lockForUpdate()->find($id);

                if (!$product || $product->quantity < $quantity) {
                    return response()->json(['error' => "Sản phẩm '{$product->name}' không đủ tồn kho."], 400);
                }

                $price = $product->sale > 0 ? $product->price * (1 - $product->sale / 100) : $product->price;
                $finalPrice = $price * $discountMultiplier;

                History::create([
                    'id_user' => $user['id'],
                    'id_product' => $id,
                    'price' => $finalPrice,
                    'quantity' => $quantity,
                    'status' => 0,
                    'order_code' => $orderCode,
                    'payment_method' => $request->paymentMethod,
                    'address' => $user['address'],
                    'note' => $user['note'] ?? null,
                ]);

                $product->decrement('quantity', $quantity);
                $product->increment('quantity_sold', $quantity);
            }

            DB::commit();
            return response()->json(['message' => 'Đặt hàng thành công!', 'orderCode' => $orderCode], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Đã xảy ra lỗi: ' . $e->getMessage()], 500);
        }
    }
}