<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\History;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string',
        ]);

        $userId = $request->user()->id;

        $exists = Review::where('user_id', $userId)
            ->where('product_id', $id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Bạn đã đánh giá sản phẩm này rồi'], 409);
        }

        $review = Review::create([
            'user_id' => $userId,
            'product_id' => $id,
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
