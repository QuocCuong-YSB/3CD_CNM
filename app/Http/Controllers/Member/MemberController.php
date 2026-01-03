<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateMemberRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function getProfile(Request $request)
    {
        $user = auth()->user();

        return response()->json([
            'data' => new UserResource($user)
        ],200);
    }
    public function update(UpdateMemberRequest $request){
        $authUser  = auth()->user();
        $user = User::find($authUser ->id);
        $data = $request->validated();
        $dataimg=[];
        if ($request->hasFile('avatar')) {
            foreach ($request->file('avatar') as $file) {
                $filename = time() . '_' . $file->getClientOriginalName();

                $file->move(public_path('avatars'), $filename);

                $dataimg[] = 'avatars/' . $filename;
            }
        }
        $data['password'] = bcrypt($data['password']);
        $data['avatar']=json_encode($dataimg);
        $user->fill($data);
        $user->save();

        return response()->json([
            'message' => 'Cập nhật user thành công',
            'data' => new UserResource($user)
        ]);
    }
}
