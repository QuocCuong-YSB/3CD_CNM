<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'order_id' => 'required|exists:orders,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
        ]);

        $userId = $request->user()->id;

        $order = Order::where('id', $request->order_id)
            ->where('user_id', $userId)
            ->where('status', 'delivered')
            ->first();

        if (!$order) {
            return response()->json([
                'message' => 'Bạn chưa mua hoặc đơn hàng chưa hoàn thành',
            ], 403);
        }

        $hasProduct = $order->orderItems()
            ->where('product_id', $request->product_id)
            ->exists();

        if (!$hasProduct) {
            return response()->json([
                'message' => 'Sản phẩm không nằm trong đơn hàng',
            ], 403);
        }

        $exists = Review::where('user_id', $userId)
            ->where('product_id', $request->product_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Bạn đã đánh giá sản phẩm này rồi',
            ], 409);
        }

        $review = Review::addReview([
            'user_id' => $userId,
            'product_id' => $request->product_id,
            'order_id' => $order->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'message' => 'Đánh giá thành công',
            'data' => $review,
        ], 201);
    }

    public function getByProduct($productId)
    {
        $reviews = Review::getReviewsByProduct($productId);

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ]);
    }
}
