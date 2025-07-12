<?php

namespace App\Http\Requests\Customer;
use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
class CreateCustomerRequest extends FormRequest
{
    public function rules(){
        return[
            'first_name'     => 'nullable|string|max:50',
            'last_name'      => 'nullable|string|max:50',
            'email'          => 'required|email|max:120|unique:customers,email',
            'phone'          => 'nullable|string|max:20',
            'password'       => 'required|string|min:6|max:60',
        ];
    }

    public function messages()
    {
        return [
        'first_name.string'      => 'Họ phải là chuỗi ký tự.',
        'first_name.max'         => 'Họ không được vượt quá 50 ký tự.',

        'last_name.string'       => 'Tên phải là chuỗi ký tự.',
        'last_name.max'          => 'Tên không được vượt quá 50 ký tự.',

        'email.required'         => 'Vui lòng nhập địa chỉ email.',
        'email.email'            => 'Email không đúng định dạng.',
        'email.max'              => 'Email không được vượt quá 120 ký tự.',
        'email.unique'           => 'Email đã được sử dụng.',

        'phone.string'           => 'Số điện thoại phải là chuỗi.',
        'phone.max'              => 'Số điện thoại không được vượt quá 20 ký tự.',

        'password.required'      => 'Vui lòng nhập mật khẩu.',
        'password.string'        => 'Mật khẩu phải là chuỗi ký tự.',
        'password.min'           => 'Mật khẩu phải có ít nhất 6 ký tự.',
        'password.max'           => 'Mật khẩu không được vượt quá 60 ký tự.',
        ];
    }
}
