<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmation;
use Illuminate\Support\Facades\Validator;

class CheckoutController extends Controller
{
    public function placeOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user' => 'required|array',
            'user.id' => 'required',
            'user.name' => 'required|string',
            'user.email' => 'required|email',
            'user.phone' => 'required|string',
            'user.address' => 'required|string',
            'cart' => 'required|array',
            'paymentMethod' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user = $request->user;
        $cart = $request->cart;
        $paymentMethod = $request->paymentMethod;
        $voucherCode = $request->voucherCode;

        DB::beginTransaction();

        try {
            $totalAmount = 0;
            $items = [];
            $ecoTax = count($cart) > 0 ? 2 : 0;

            foreach ($cart as $id => $quantity) {
                $product = Product::lockForUpdate()->find($id);

                if (!$product) {
                    return response()->json(['error' => "Sản phẩm (ID: $id) không tồn tại."], 404);
                }

                if ($product->quantity < $quantity) {
                    return response()->json(['error' => "Sản phẩm '{$product->name}' không đủ tồn kho."], 400);
                }

                $price = $product->price;
                if ($product->sale > 0) {
                    $price = $product->price * (1 - $product->sale / 100);
                }

                $totalAmount += $price * $quantity;

                $product->quantity -= $quantity;
                $product->quantity_sold += $quantity;
                $product->save();

                $items[] = [
                    'product_id' => $id,
                    'price' => $price,
                    'quantity' => $quantity
                ];
            }

            // Apply Voucher logic (NEWUSER - 5%)
            $discount = 0;
            if ($voucherCode === 'NEWUSER') {
                $hasOrders = Order::where('user_id', $request->user()->id)->exists();
                if (!$hasOrders) {
                    $discount = $totalAmount * 0.05;
                }
            }

            $finalTotal = $totalAmount - $discount + $ecoTax;

            $order = Order::create([
                'user_id' => $request->user()->id,
                'order_code' => Order::generateOrderCode(),
                'total_amount' => $finalTotal,
                'payment_method' => $paymentMethod,
                'status' => 0, // Waiting
                'name' => $user['name'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'address' => $user['address'],
                'note' => $user['note'] ?? null,
                'eco_tax' => $ecoTax,
                'discount' => $discount,
                'voucher_code' => $discount > 0 ? $voucherCode : null,
            ]);

            foreach ($items as $item) {
                $order->items()->create($item);
            }

            DB::commit();

            // Send Email
            try {
                Mail::to($user['email'])->send(new OrderConfirmation($order));
            } catch (\Exception $e) {
                \Log::error("Email failed for order {$order->order_code}: " . $e->getMessage());
            }

            return response()->json([
                'message' => 'Đặt hàng thành công!',
                'data' => $order->load('items.product')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Checkout error: " . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi trong quá trình đặt hàng.'], 500);
        }
    }
}
