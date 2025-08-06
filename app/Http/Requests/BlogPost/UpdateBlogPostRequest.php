<?php

namespace App\Http\Requests\BlogPost;
use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBlogPostRequest extends FormRequest
{
    public function rules()
    {
        return [
            'category_blog_id' => ['required', 'exists:categories_blog,id'],
            'title' => ['required', 'string', 'max:255'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'summary' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'keywords' => ['nullable', 'string', 'max:255'],
            'view_count' => ['nullable', 'integer', 'min:0'],
            'published' => ['required', 'boolean'],
            'published_at' => ['nullable', function ($attribute, $value, $fail) {
                if ($value !== null && $value !== '') {
                    $date = \DateTime::createFromFormat('Y-m-d\TH:i', $value);
                    if ($date === false) {
                        $fail('Định dạng ngày xuất bản không hợp lệ. Vui lòng chọn lại ngày.');
                    }
                }
            }],
        ];
    }

    public function messages()
    {
        return [
            'category_blog_id.required' => 'Danh mục bài viết là bắt buộc.',
            'category_blog_id.exists' => 'Danh mục bài viết không tồn tại.',

            'title.required' => 'Tiêu đề bài viết là bắt buộc.',
            'title.max' => 'Tiêu đề không được vượt quá 255 ký tự.',

            'thumbnail.image' => 'Tệp tải lên phải là hình ảnh.',
            'thumbnail.mimes' => 'Ảnh chỉ được chấp nhận định dạng: jpg, jpeg, png, hoặc webp.',
            'thumbnail.max' => 'Kích thước ảnh không được vượt quá 2MB.',

            'summary.max' => 'Tóm tắt không được vượt quá 500 ký tự.',

            'content.required' => 'Nội dung bài viết là bắt buộc.',

            'meta_title.max' => 'Meta title không được vượt quá 255 ký tự.',
            'meta_description.max' => 'Meta description không được vượt quá 255 ký tự.',
            'keywords.max' => 'Từ khóa không được vượt quá 255 ký tự.',

            'view_count.integer' => 'Số lượt xem phải là số nguyên.',
            'view_count.min' => 'Số lượt xem không thể nhỏ hơn 0.',

            'published.required' => 'Trạng thái xuất bản là bắt buộc.',
            'published.boolean' => 'Trạng thái xuất bản không hợp lệ.',
        ];
    }
}
