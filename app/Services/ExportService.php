<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Log;

class ExportService
{
    /**
     * Xuất danh sách sản phẩm ra file CSV
     *
     * @return array
     */
    public function exportProductsToCsv()
    {
        try {
            $products = Product::query()
                ->with(['images' => function($query) {
                    $query->orderBy('sort_order', 'asc');
                }])
                ->get();

            // Tạo tên file với timestamp
            $filename = 'products_export_' . date('Y-m-d_H-i-s') . '.csv';
            $filepath = storage_path('app/exports/' . $filename);

            // Tạo thư mục nếu chưa tồn tại
            if (!file_exists(dirname($filepath))) {
                mkdir(dirname($filepath), 0755, true);
            }

            // Mở file để ghi
            $file = fopen($filepath, 'w');

            // Ghi header CSV với BOM để hỗ trợ tiếng Việt
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['ID', 'Tên sản phẩm', 'Hình ảnh'], ',');

            // Ghi dữ liệu từng sản phẩm
            foreach ($products as $product) {
                $imageUrls = [];

                // Lấy tất cả URL hình ảnh của sản phẩm
                if ($product->images->count() > 0) {
                    foreach ($product->images as $image) {
                        $imageUrls[] = $image->img_url;
                    }
                }

                // Nối các URL hình ảnh bằng dấu chấm phẩy
                $imagesString = implode('; ', $imageUrls);

                // Ghi dòng dữ liệu
                fputcsv($file, [
                    $product->prod_id,
                    $product->name,
                    $imagesString
                ], ',');
            }

            fclose($file);

            return [
                'success' => true,
                'filepath' => $filepath,
                'filename' => $filename,
                'total_products' => $products->count()
            ];

        } catch (\Exception $e) {
            Log::error('Lỗi khi xuất CSV sản phẩm: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xuất file CSV: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Download file CSV
     *
     * @param string $filename
     * @return array
     */
    public function downloadCsv($filename)
    {
        $filepath = storage_path('app/exports/' . $filename);

        if (!file_exists($filepath)) {
            return [
                'success' => false,
                'message' => 'File không tồn tại'
            ];
        }

        return [
            'success' => true,
            'filepath' => $filepath,
            'filename' => $filename
        ];
    }
}
