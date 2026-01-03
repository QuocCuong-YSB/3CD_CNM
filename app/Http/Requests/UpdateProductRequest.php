<?php

namespace App\Http\Requests;



class UpdateProductRequest extends BaseRequest
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
            'sale'        => 'required|numeric',

            'image'   => 'nullable|array|max:3',
            'image.*' => 'file|max:2048',

            // ảnh cần xóa (JSON array)
            'imageDelete' => 'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'id_category.required' => 'Thiếu category',
            'id_category.exists'   => 'Loại sản phẩm không tồn tại',

            'id_brand.required' => 'Thiếu brand',
            'id_brand.exists'   => 'Thương hiệu không tồn tại',

            'name.required' => 'Thiếu name',
            'price.required' => 'Thiếu price',
            'price.numeric' => 'Price phải là số',

            'detail.required' => 'Thiếu detail',

            'quantity.required' => 'Thiếu quantity',
            'quantity.min' => 'Số lượng phải lớn hơn 0',

            'image.array'    => 'Image phải là mảng',
            'image.max'      => 'Chỉ được chọn tối đa 3 file',
            'image.*.file'   => 'File upload không hợp lệ',
            'image.*.max'    => 'Dung lượng file phải nhỏ hơn 2MB',
        ];
    }
}
