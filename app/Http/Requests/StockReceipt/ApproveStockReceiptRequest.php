<?php

namespace App\Http\Requests\StockReceipt;
use Illuminate\Foundation\Http\FormRequest;
class ApproveStockReceiptRequest extends FormRequest
{
    public function rules(){
        return [
            'status' => 'required|in:đã duyệt,đã nhập,đã hủy',
        ];
    }

    public function messages(){
        return [
            'status.required' => 'Vui lòng chọn trạng thái cần cập nhật.',
            'status.in' => 'Trạng thái không hợp lệ.',
        ];
    }
}