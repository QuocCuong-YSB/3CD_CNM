<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\History;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // Lấy danh sách đơn hàng cho Member (History)
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $orders = History::with('product')
            ->where('id_user', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $orders
        ]);
    }

    // Hủy đơn hàng (Chỉ khi status = 0 - Chờ xác nhận)
    public function cancel(Request $request, $id)
    {
        $userId = $request->user()->id;
        $order = History::where('id_user', $userId)->where('id', $id)->first();

        if (!$order) {
            return response()->json(['error' => 'Đơn hàng không tồn tại.'], 404);
        }

        if ($order->status !== 0) {
            return response()->json(['error' => 'Đơn hàng không ở trạng thái có thể hủy.'], 400);
        }

        DB::beginTransaction();
        try {
            // Hủy toàn bộ item có cùng mã đơn hàng
            History::where('order_code', $order->order_code)
                ->update(['status' => 3]);

            // Hoàn tồn kho
            $items = History::where('order_code', $order->order_code)->get();
            foreach ($items as $item) {
                $product = $item->product;
                if ($product) {
                    $product->increment('quantity', $item->quantity);
                    $product->decrement('quantity_sold', $item->quantity);
                }
            }

            DB::commit();
            return response()->json(['message' => 'Hủy đơn hàng thành công!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Lỗi hệ thống khi hủy đơn hàng.'], 500);
        }
    }

    // Xác nhận đã nhận hàng (Member)
    public function markAsDelivered(Request $request, $id)
    {
        $userId = $request->user()->id;
        $order = History::where('id_user', $userId)->where('id', $id)->first();

        if (!$order || $order->status !== 1) {
            return response()->json(['error' => 'Đơn hàng không hợp lệ.'], 400);
        }

        History::where('order_code', $order->order_code)->update(['status' => 2]);

        return response()->json(['message' => 'Xác nhận đã nhận hàng thành công!']);
    }
}

