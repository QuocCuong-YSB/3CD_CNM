<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;

class RegisterRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => ['required','regex:/^(0|\+84)[0-9]{9}$/'],'address' => 'required|string',
            'id_country' => 'required|exists:countries,id',
            'avatar' => 'required|array',
            'avatar.*' => 'image|mimes:jpg,jpeg,png,gif|max:1024',
        ];
    }
    public function messages()
    {
        return [
            'name.required' => 'Vui lòng nhập tên',

            'email.required' => 'Vui lòng nhập email',
            'email.email' => 'Email không hợp lệ',
            'email.unique' => 'Email đã tồn tại',

            'password.required' => 'Vui lòng nhập password',
            'password.min' => 'Password tối thiểu 6 ký tự',

            'phone.required' => 'Vui lòng nhập phone',
            'phone.regex' => 'Phone không hợp lệ',

            'address.required' => 'Vui lòng nhập address',

            'avatar.required' => 'Vui lòng upload avatar',
            'avatar.image' => 'File phải là hình ảnh',
            'avatar.mimes' => 'Chỉ chấp nhận JPEG, PNG hoặc GIF',
            'avatar.max' => 'Dung lượng file phải nhỏ hơn 1MB',

            'id_country.required' => 'Vui lòng chọn quốc gia',
            'id_country.exists' => 'Quốc gia không hợp lệ',
        ];
    }
}
