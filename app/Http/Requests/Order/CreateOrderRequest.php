<?php

namespace App\Http\Requests\Order;
use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
class CreateOrderRequest extends FormRequest
{
    public function rules()
    {
        return [
            // 'cust_id' => 'required|exists:customers,cust_id',
            'order_status' => 'required|in:pending,paid,shipped,completed,cancelled',
            'payment_status' => 'required|in:unpaid,paid,refunded',
            'sub_total' => 'nullable|numeric|min:0',
            'shipping_fee' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'name' => 'required|string|min:2|max:100',
            'phone' => ['required', 'regex:/^(0|\\+84)[0-9]{9}$/'],
            'total_amount' => 'nullable|numeric|min:0',
            'address_customer' => 'required|string',
            'order_items' => 'required|array|min:1',
            'order_items.*.prod_id' => 'required|integer|exists:products,prod_id',
            'order_items.*.qty' => 'required|integer|min:1',
            'order_items.*.unit_price' => 'required|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            // 'cust_id.required' => 'Khách hàng là bắt buộc.',
            // 'cust_id.exists' => 'Khách hàng không tồn tại trong hệ thống.',

            'order_status.required' => 'Trạng thái đơn hàng là bắt buộc.',
            'order_status.in' => 'Trạng thái đơn hàng không hợp lệ.',

            'payment_status.required' => 'Trạng thái thanh toán là bắt buộc.',
            'payment_status.in' => 'Trạng thái thanh toán không hợp lệ.',
            'name.required' => 'Vui lòng nhập họ và tên.',

            'name.string' => 'Họ và tên không hợp lệ.',
            'name.min' => 'Họ và tên phải có ít nhất 2 ký tự.',
            'name.max' => 'Họ và tên không được vượt quá 100 ký tự.',

            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.string' => 'Số điện thoại không hợp lệ.',
            'phone.regex' => 'Số điện thoại không đúng định dạng (VD: 0912345678 hoặc +84912345678).',

            'sub_total.numeric' => 'Tổng phụ phải là số.',
            'sub_total.min' => 'Tổng phụ không được âm.',

            'shipping_fee.required' => 'Phí vận chuyển là bắt buộc.',
            'shipping_fee.numeric' => 'Phí vận chuyển phải là số.',
            'shipping_fee.min' => 'Phí vận chuyển không được âm.',

            'discount.numeric' => 'Số tiền giảm giá phải là số.',
            'discount.min' => 'Số tiền giảm giá không được âm.',

            'total_amount.numeric' => 'Tổng thanh toán phải là số.',
            'total_amount.min' => 'Tổng thanh toán không được âm.',

            'order_items.required' => 'Vui lòng thêm ít nhất 1 sản phẩm.',
            'order_items.*.prod_id.required' => 'Mỗi sản phẩm phải có mã sản phẩm.',
            'order_items.*.prod_id.exists' => 'Sản phẩm không tồn tại.',
            'order_items.*.qty.required' => 'Mỗi sản phẩm phải có số lượng.',
            'order_items.*.qty.min' => 'Số lượng sản phẩm phải ít nhất là 1.',
            'order_items.*.unit_price.required' => 'Giá tiền là bắt buộc cho từng sản phẩm.',
        ];
    }
}
