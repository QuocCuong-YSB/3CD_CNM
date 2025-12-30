<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function show($id): JsonResponse
    {
        $user = User::findOrFail($id);
        $data = $user->toArray();
        unset($data['password'], $data['level']);

        return response()->json($data);
    }
}
