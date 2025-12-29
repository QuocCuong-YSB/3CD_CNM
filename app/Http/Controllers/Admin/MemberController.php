<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index()
    {
        $users = User::where('level', 0)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $users,
        ],200);
    }
    public function updateStatus(Request $request, $id)
    {
        $user = User::where('level', 0)->findOrFail($id);

        $user->is_active = !$user->is_active;
        $user->save();

        return response()->json([
            'message' => $user->is_active
                ? 'Mở khóa tài khoản thành công'
                : 'Khóa tài khoản thành công',
            'data' => $user,
        ],200);
    }
}
