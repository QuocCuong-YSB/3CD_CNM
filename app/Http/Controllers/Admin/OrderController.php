<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;

class OrderController extends Controller
{
    /**
     * Get all orders
     */
    public function index()
    {
        $orders = Order::with('items.product', 'user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $orders
        ]);
    }

    /**
     * Confirm/Update order status
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['error' => 'Đơn hàng không tồn tại.'], 404);
        }

        $request->validate([
            'status' => 'required|integer|in:1,2,3'
        ]);

        $order->status = $request->status;
        $order->save();

        return response()->json(['message' => 'Cập nhật trạng thái đơn hàng thành công!']);
    }
}
