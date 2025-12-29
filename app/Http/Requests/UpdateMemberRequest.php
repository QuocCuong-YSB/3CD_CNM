<?php

namespace App\Http\Requests;

class UpdateMemberRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'       => 'required|string',
            'password'   => 'required|min:6',
            'phone'      => 'nullable|string',
            'address'    => 'nullable|string',
            'id_country' => 'required|exists:countries,id',
            'avatar.*' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:1024',
            'avatar' => 'required|array',
        ];
    }
    public function messages()
    {
        return [
            'name.required' => 'Tên không được để trống',

            'password.required' => 'Mật khẩu không được để trống',
            'password.min' => 'Mật khẩu phải ít nhất 6 ký tự',

            'id_country.required' => 'Vui lòng chọn quốc gia',
            'id_country.exists' => 'Quốc gia không tồn tại',

            'avatar.required' => 'Vui lòng upload avatar',
            'avatar.image' => 'File phải là hình ảnh',
            'avatar.mimes' => 'Chỉ chấp nhận JPEG, PNG hoặc GIF',
            'avatar.max' => 'Dung lượng file phải nhỏ hơn 1MB',
        ];
    }
}
