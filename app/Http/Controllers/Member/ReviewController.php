<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\History;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function canReview(Request $request, $id)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'can_review' => false,
                'reason' => 'not_logged_in'
            ]);
        }

        $order = History::where('id_user', $user->id)
            ->where('id_product', $id) // dùng $id
            ->where('status', History::STATUS_COMPLETED)
            ->first();

        if (!$order) {
            return response()->json([
                'can_review' => false,
                'reason' => 'not_purchased'
            ]);
        }

        $reviewed = Review::where('user_id', $user->id)
            ->where('product_id', $id)
            ->exists();

        if ($reviewed) {
            return response()->json([
                'can_review' => false,
                'reason' => 'already_reviewed'
            ]);
        }

        return response()->json([
            'can_review' => true
        ]);
    }

    public function store(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
            'order_code' => 'required|string',
        ]);

        $user = $request->user();
        $orderCode = $request->order_code;

        $order = History::where('id_user', $user->id)
            ->where('id_product', $id)
            ->where('status', History::STATUS_COMPLETED)
            ->where('order_code', $orderCode)
            ->first();

        if (!$order) {
            return response()->json([
                'error' => 'Đơn hàng không tồn tại hoặc chưa hoàn thành'
            ], 403);
        }

        $exists = Review::where('user_id', $user->id)
            ->where('product_id', $id)
            ->where('order_code', $orderCode)
            ->exists();

        if ($exists) {
            return response()->json([
                'error' => 'Bạn đã đánh giá sản phẩm này trong đơn hàng này rồi'
            ], 409);
        }

        $review = Review::create([
            'user_id' => $user->id,
            'product_id' => $id,
            'order_code' => $orderCode,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'message' => 'Đánh giá thành công',
            'data' => $review,
        ], 201);
    }

    public function getByProduct($id)
    {
        $reviews = Review::getReviewsByProduct($id);

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ]);
    }

    public function getCompletedOrders(Request $request, $id)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập',
            ], 401);
        }

        $orders = History::where('id_user', $user->id)
            ->where('id_product', $id) 
            ->where('status', History::STATUS_COMPLETED)
            ->orderByDesc('created_at')
            ->get(['order_code', 'created_at']);

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }
}
