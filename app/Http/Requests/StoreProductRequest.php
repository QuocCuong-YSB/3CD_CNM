<?php

namespace App\Http\Requests;


class StoreProductRequest extends BaseRequest
{
     public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_category' => 'required|exists:categories,id',
            'id_brand'    => 'required|exists:brands,id',
            'name'        => 'required|string',
            'price'       => 'required|numeric',
            'detail'      => 'required|string',
            'quantity'    => 'required|integer|min:1',
            'status'      => 'required|numeric',
            'sale'        => 'required|numeric',

            'image'       => 'nullable|array|max:3',
            'image.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:1024',
        ];
    }

    public function messages(): array
    {
        return [
            'id_category.required' => 'Thiếu category',
            'id_category.exists'   => 'Loại sản phẩm không tồn tại trong hệ thống',

            'id_brand.required' => 'Thiếu brand',
            'id_brand.exists'   => 'Thương hiệu không tồn tại trong hệ thống',

            'name.required'     => 'Thiếu name',
            'price.required'    => 'Thiếu price',
            'detail.required'   => 'Thiếu detail',

            'quantity.required' => 'Thiếu quantity',
            'quantity.min'      => 'Số lượng phải lớn hơn 0',

            'image.array'       => 'Image phải là mảng',
            'image.max'         => 'Chỉ được chọn tối đa 3 file',

            'image.*.mimes'     => 'Định dạng file không hợp lệ. Chỉ chấp nhận JPEG, PNG hoặc GIF',
            'image.*.max'       => 'Dung lượng file quá lớn. Vui lòng chọn file nhỏ hơn 1MB',
        ];
    }
}
