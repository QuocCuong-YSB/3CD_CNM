<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;

class LoginRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'email' => 'required|string|max:255',
            'password' => 'required|string|min:6',
            'level' => 'required|integer',
        ];
    }
    public function messages()
    {
        return [
            'email.required' => 'Vui lòng nhập email',
            'email.email' => 'Email không hợp lệ',

            'password.required' => 'Vui lòng nhập password',
            'password.min' => 'Password tối thiểu 6 ký tự',

            'level.required' => 'Thiếu level',
            'level.integer' => 'level là số',
        ];
    }
}
