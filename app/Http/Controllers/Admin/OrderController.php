<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\History;

class OrderController extends Controller
{
    // Lấy danh sách tất cả đơn hàng (Admin)
    public function index()
    {
        $orders = History::with(['product', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $orders
        ]);
    }

    // Cập nhật trạng thái đơn hàng (Admin)
    public function updateStatus(Request $request, $id)
    {
        $order = History::find($id);
        if (!$order) {
            return response()->json(['error' => 'Đơn hàng không tồn tại.'], 404);
        }

        $request->validate([
            'status' => 'required|integer|in:0,1,2,3'
        ]);

        // Cập nhật trạng thái toàn bộ items trong cùng mã đơn hàng
        History::where('order_code', $order->order_code)
            ->update(['status' => $request->status]);

        return response()->json(['message' => 'Cập nhật trạng thái thành công!']);
    }
}

