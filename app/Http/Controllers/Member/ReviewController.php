<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\History;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function canReview(Request $request, $productId)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'can_review' => false,
                'reason' => 'not_logged_in'
            ]);
        }

        $order = History::where('id_user', $user->id)
            ->where('id_product', $productId)
            ->where('status', History::STATUS_COMPLETED)
            ->first();

        if (!$order) {
            return response()->json([
                'can_review' => false,
                'reason' => 'not_purchased'
            ]);
        }

        $reviewed = Review::where('user_id', $user->id)
            ->where('product_id', $productId)
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

    public function store(Request $request, $productId)
    {
        $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
        ]);

        $user = $request->user();

        $order = History::getCompletedOrder($user->id, $productId);

        if (!$order) {
            return response()->json([
                'error' => 'Bạn cần mua và nhận hàng thành công trước khi đánh giá'
            ], 403);
        }

        $exists = Review::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->exists();

        if ($exists) {
            return response()->json([
                'error' => 'Bạn đã đánh giá sản phẩm này rồi'
            ], 409);
        }

        $review = Review::create([
            'user_id'    => $user->id,
            'product_id' => $productId,
            'order_code' => $order->order_code,
            'rating'     => $request->rating,
            'comment'    => $request->comment,
        ]);

        return response()->json([
            'message' => 'Đánh giá thành công',
            'data'    => $review,
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
