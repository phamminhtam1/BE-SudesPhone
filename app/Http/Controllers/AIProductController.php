<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;

class AIProductController extends Controller
{
    public function generate(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $name = $request->input('name');
        $prompt = "
Bạn là một chuyên gia mô tả sản phẩm. Viết nội dung cho sản phẩm: \"$name\" gồm:
1. Mô tả ngắn (2 câu).
2. Mô tả dài (5-7 câu).
3. 5 từ khóa SEO (mảng JSON).
4. 3-5 thông số kỹ thuật dưới dạng [{\"spec_key\": \"Tên\", \"spec_value\": \"Giá trị\"}].

Kết quả trả về dưới dạng JSON.
        ";

        $result = OpenAI::chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.7,
        ]);

        $response = $result->choices[0]->message->content;

        // Bắt buộc yêu cầu ChatGPT trả JSON — xử lý lỗi nếu không đúng format
        try {
            return response()->json(json_decode($response, true));
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Lỗi phân tích kết quả từ AI.',
                'raw_response' => $response
            ], 500);
        }
    }
}

