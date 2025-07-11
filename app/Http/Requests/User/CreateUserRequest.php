<?php

namespace App\Http\Requests\User;
use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
class CreateUserRequest extends FormRequest
{
    public function rules(){
        return[
            'branch_id' => 'required|integer',
            'role_id' =>'required|integer',
            'name'=> 'required|string',
            'email'=> 'required|string|email|unique:users,email',
            'phone' => ['required', 'regex:/^(0|\\+84)[0-9]{9}$/'],
            'hire_date' =>'required|date',
            'salary' => 'required|numeric|min:0|max:9999999999.99',
            'password' => 'required|min:6|confirmed'
        ];
    }

    public function messages(){
        return[
            'branch_id.required' => 'Vui lòng chọn chi nhánh.',
            'branch_id.integer' => 'Chi nhánh không hợp lệ.',
            'role_id.required' => 'Vui lòng chọn vai trò.',
            'role_id.integer' => 'Vai trò không hợp lệ.',
            'name.required' => 'Vui lòng nhập tên.',
            'name.string' => 'Tên phải là chuỗi.',
            'name.max' => 'Tên không được vượt quá 255 ký tự.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã được sử dụng.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.regex' => 'Số điện thoại không đúng định dạng.',
            'hire_date.required' => 'Vui lòng chọn ngày thuê.',
            'hire_date.date' => 'Ngày thuê không hợp lệ.',
            'salary.required' => 'Vui lòng nhập lương.',
            'salary.numeric' => 'Lương phải là số.',
            'salary.min' => 'Lương không được âm.',
            'salary.max' => 'Lương quá lớn.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
        ];
    }
}