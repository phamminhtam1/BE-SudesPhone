<?php

namespace App\Http\Requests\StockReceipt;
use Illuminate\Foundation\Http\FormRequest;
class CreateStockReceiptRequest extends FormRequest
{
    public function rules(){
        return[
            'branch_id'    => 'required|integer|exists:branches,branch_id',
            'supplier_id'  => 'nullable|integer|exists:suppliers,supp_id',
            'sku'          => 'required|string|exists:products,sku',
            'qty'          => 'required|integer|min:1',
            'unit_price'   => 'required|numeric|min:0',
            'note'         => 'nullable|string|max:1000',
        ];
    }

    public function messages(){
        return[
            'branch_id.required'   => 'Vui lòng chọn chi nhánh.',
            'branch_id.integer'    => 'Chi nhánh không hợp lệ.',
            'branch_id.exists'     => 'Chi nhánh không tồn tại.',
            'supplier_id.integer'  => 'Nhà cung cấp không hợp lệ.',
            'supplier_id.exists'   => 'Nhà cung cấp không tồn tại.',
            'sku.required'         => 'Vui lòng quét hoặc nhập mã SKU.',
            'sku.exists'           => 'Mã SKU không tồn tại trong hệ thống.',
            'qty.required'         => 'Vui lòng nhập số lượng.',
            'qty.integer'          => 'Số lượng phải là số nguyên.',
            'qty.min'              => 'Số lượng tối thiểu là 1.',
            'unit_price.required'  => 'Vui lòng nhập đơn giá.',
            'unit_price.numeric'   => 'Đơn giá phải là số.',
            'unit_price.min'       => 'Đơn giá không được âm.',
            'note.string'          => 'Ghi chú phải là văn bản.',
            'note.max'             => 'Ghi chú không được vượt quá 1000 ký tự.',
        ];
    }
}