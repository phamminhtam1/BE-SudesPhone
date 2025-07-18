<?php

namespace App\Http\Requests\Cart;
use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
class CreateCartRequest extends FormRequest
{
    public function rules()
    {
        return [
            'product_id' => 'required|integer|exists:products,id',
            'quantity'   => 'required|integer|min:1'
        ];
    }

    public function messages()
    {
        return [
            'product_id.required' => 'Vui lòng chọn sản phẩm.',
            'product_id.integer'  => 'ID sản phẩm phải là số nguyên.',
            'product_id.exists'   => 'Sản phẩm không tồn tại trong hệ thống.',

            'quantity.required'   => 'Vui lòng nhập số lượng.',
            'quantity.integer'    => 'Số lượng phải là số nguyên.',
            'quantity.min'        => 'Số lượng tối thiểu là 1.'
        ];
    }
}
