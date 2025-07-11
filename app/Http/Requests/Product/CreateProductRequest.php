<?php

namespace App\Http\Requests\Product;
use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
class CreateProductRequest extends FormRequest
{
    public function rules(){
        return[
            'cat_id' => 'required|string',
            'name'=> 'required|string',
            'short_desc'=>'required|string',
            'long_desc' =>'required|string',
            'price'=> 'required|numeric|min:0|max:9999999999.99',
            'discount_price'=> 'required|numeric|min:0|max:9999999999.99',
            'warranty_months'=> 'required|int',
            'stock_qty'=>'nullable|int',
            'keywords'=> 'required|string',
            'images'=> 'nullable|array',
            'images.*'=> 'image|mimes:jpg,jpeg,png,webp|max:5120',
            'specs' => 'nullable|array',
            'specs.*.spec_key' => 'required|string',
            'specs.*.spec_value' => 'required|string',
        ];
    }

    public function messages()
{
    return [
        'cat_id.required' => 'Vui lòng chọn danh mục.',
        'cat_id.string' => 'Danh mục không hợp lệ.',
        'name.required' => 'Vui lòng nhập tên sản phẩm.',
        'name.string' => 'Tên sản phẩm không hợp lệ.',
        'short_desc.required' => 'Vui lòng nhập mô tả ngắn.',
        'short_desc.string' => 'Mô tả ngắn không hợp lệ.',
        'long_desc.required' => 'Vui lòng nhập mô tả chi tiết.',
        'long_desc.string' => 'Mô tả chi tiết không hợp lệ.',
        'price.required' => 'Vui lòng nhập giá sản phẩm.',
        'price.numeric' => 'Giá phải là số.',
        'price.min' => 'Giá không được âm.',
        'price.max' => 'Giá quá lớn.',
        'discount_price.required' => 'Vui lòng nhập giá khuyến mãi.',
        'discount_price.numeric' => 'Giá khuyến mãi phải là số.',
        'discount_price.min' => 'Giá khuyến mãi không được âm.',
        'discount_price.max' => 'Giá khuyến mãi quá lớn.',
        'warranty_months.required' => 'Vui lòng nhập thời gian bảo hành.',
        'warranty_months.int' => 'Thời gian bảo hành phải là số nguyên.',
        'stock_qty.int' => 'Số lượng trong kho phải là số nguyên.',
        'keywords.required' => 'Vui lòng nhập từ khóa.',
        'keywords.string' => 'Từ khóa không hợp lệ.',
        'images.array' => 'Ảnh không đúng định dạng.',
        'images.*.image' => 'Từng ảnh phải là một tệp hình ảnh.',
        'images.*.mimes' => 'Ảnh phải có định dạng: jpg, jpeg, png, webp.',
        'images.*.max' => 'Mỗi ảnh không được vượt quá 5MB.',
        'specs.array' => 'Thông số kỹ thuật không đúng định dạng.',
        'specs.*.spec_key.required' => 'Vui lòng nhập tên thông số kỹ thuật.',
        'specs.*.spec_key.string' => 'Tên thông số kỹ thuật không hợp lệ.',
        'specs.*.spec_value.required' => 'Vui lòng nhập giá trị thông số kỹ thuật.',
        'specs.*.spec_value.string' => 'Giá trị thông số kỹ thuật không hợp lệ.',
    ];
}
}