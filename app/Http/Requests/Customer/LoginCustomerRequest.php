<?php

namespace App\Http\Requests\Customer;
use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
class LoginCustomerRequest extends FormRequest
{
    public function rules(){
        return[
            'email'          => 'required|email',
            'password'       => 'required|string|min:6',
        ];
    }

    public function messages()
{
    return [
    'email.required'         => 'Vui lòng nhập địa chỉ email.',
    'email.email'            => 'Email không đúng định dạng.',

    'password.required'      => 'Vui lòng nhập mật khẩu.',
    'password.string'        => 'Mật khẩu phải là chuỗi ký tự.',
    'password.min'           => 'Mật khẩu phải có ít nhất 6 ký tự.',
    ];
}
}
