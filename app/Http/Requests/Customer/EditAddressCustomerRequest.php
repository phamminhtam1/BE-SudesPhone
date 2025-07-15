<?php

namespace App\Http\Requests\Customer;
use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
class EditAddressCustomerRequest extends FormRequest
{
    public function rules()
    {
        return [
            'label'        => 'nullable|string|max:100',
            'line'        => 'required|string|max:255',
            'city'         => 'required|string|max:100',
            'region'       => 'nullable|string|max:100',
            'ward'       => 'nullable|string|max:100',
            'is_default'   => 'required|boolean',
        ];
    }

    public function messages()
    {
        return [
            'label.string'         => 'Nhãn địa chỉ phải là chuỗi.',
            'label.max'            => 'Nhãn địa chỉ không được vượt quá 100 ký tự.',

            'line.required'       => 'Vui lòng nhập địa chỉ chi tiết.',
            'line.string'         => 'Địa chỉ phải là chuỗi.',
            'line.max'            => 'Địa chỉ không được vượt quá 255 ký tự.',

            'city.required'        => 'Vui lòng nhập thành phố.',
            'city.string'          => 'Thành phố phải là chuỗi.',
            'city.max'             => 'Thành phố không được vượt quá 100 ký tự.',

            'region.string'        => 'Tỉnh / Vùng phải là chuỗi.',
            'region.max'           => 'Tỉnh / Vùng không được vượt quá 100 ký tự.',

            'ward.string'        => 'Quận / Huyện phải là chuỗi.',
            'ward.max'           => 'Quận / Huyện không được vượt quá 100 ký tự.',

            'is_default.required'  => 'Vui lòng xác định địa chỉ mặc định.',
            'is_default.boolean'   => 'Giá trị địa chỉ mặc định phải là true hoặc false.',
        ];
    }

}
