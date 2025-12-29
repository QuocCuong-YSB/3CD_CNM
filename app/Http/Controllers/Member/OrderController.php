<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Get orders
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $orders = Order::with('items.product')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $orders
        ]);
    }

    /**
     * Cancel order
     */
    public function cancel(Request $request, $id)
    {
        $order = Order::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (!$order) {
            return response()->json(['error' => 'Đơn hàng không tồn tại.'], 404);
        }

        if ($order->status !== 0) {
            return response()->json(['error' => 'Chỉ có thể hủy đơn hàng đang ở trạng thái chờ xác nhận.'], 400);
        }

        DB::beginTransaction();
        try {
            $order->status = 3;
            $order->save();

            foreach ($order->items as $item) {
                $product = $item->product;
                if ($product) {
                    $product->quantity += $item->quantity;
                    $product->quantity_sold -= $item->quantity;
                    $product->save();
                }
            }

            DB::commit();
            return response()->json(['message' => 'Hủy đơn hàng thành công!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Lỗi khi hủy đơn hàng.'], 500);
        }
    }

    public function markAsDelivered(Request $request, $id)
    {
        $order = Order::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (!$order) {
            return response()->json(['error' => 'Đơn hàng không tồn tại.'], 404);
        }

        if ($order->status !== 1) {
            return response()->json(['error' => 'Đơn hàng phải ở trạng thái đang giao mới có thể xác nhận.'], 400);
        }

        $order->status = 2;
        $order->save();

        return response()->json(['message' => 'Xác nhận nhận hàng thành công!']);
    }
}
