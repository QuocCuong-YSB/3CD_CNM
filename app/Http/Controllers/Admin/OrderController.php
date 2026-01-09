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
    public function updateStatus(Request $request, $order_code)
    {
        $exists = History::where('order_code', $order_code)->exists();
        if (!$exists) {
            return response()->json(['error' => 'Đơn hàng không tồn tại.'], 404);
        }

        $request->validate([
            'status' => 'required|integer|in:0,1,2,3'
        ]);

        History::where('order_code', $order_code)
            ->update(['status' => $request->status]);

        return response()->json(['message' => 'Cập nhật trạng thái thành công!']);
    }
}

