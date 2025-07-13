<?php

namespace App\Http\Requests\Customer;
use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
class CreateAddressCustomerRequest extends FormRequest
{
    public function rules()
    {
        return [
            'label'        => 'nullable|string|max:100',
            'line1'        => 'required|string|max:255',
            'line2'        => 'nullable|string|max:255',
            'city'         => 'required|string|max:100',
            'region'       => 'nullable|string|max:100',
            'postal_code'  => 'nullable|string|max:20',
            'country'      => 'required|string|max:100',
            'is_default'   => 'required|boolean',
        ];
    }

    public function messages()
    {
        return [
            'label.string'         => 'Nhãn địa chỉ phải là chuỗi.',
            'label.max'            => 'Nhãn địa chỉ không được vượt quá 100 ký tự.',

            'line1.required'       => 'Vui lòng nhập địa chỉ chi tiết (line1).',
            'line1.string'         => 'Địa chỉ phải là chuỗi.',
            'line1.max'            => 'Địa chỉ không được vượt quá 255 ký tự.',

            'line2.string'         => 'Địa chỉ bổ sung phải là chuỗi.',
            'line2.max'            => 'Địa chỉ bổ sung không được vượt quá 255 ký tự.',

            'city.required'        => 'Vui lòng nhập thành phố.',
            'city.string'          => 'Thành phố phải là chuỗi.',
            'city.max'             => 'Thành phố không được vượt quá 100 ký tự.',

            'region.string'        => 'Tỉnh / Vùng phải là chuỗi.',
            'region.max'           => 'Tỉnh / Vùng không được vượt quá 100 ký tự.',

            'postal_code.string'   => 'Mã bưu chính phải là chuỗi.',
            'postal_code.max'      => 'Mã bưu chính không được vượt quá 20 ký tự.',

            'country.required'     => 'Vui lòng nhập quốc gia.',
            'country.string'       => 'Quốc gia phải là chuỗi.',
            'country.max'          => 'Quốc gia không được vượt quá 100 ký tự.',

            'is_default.required'  => 'Vui lòng xác định địa chỉ mặc định.',
            'is_default.boolean'   => 'Giá trị địa chỉ mặc định phải là true hoặc false.',
        ];
    }

}
