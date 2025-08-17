<?php

namespace App\Http\Controllers;

use App\Services\ExportService;

class ExportController extends Controller
{
    protected $exportService;

    public function __construct(ExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    /**
     * Xuất danh sách sản phẩm ra CSV
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportProductsToCsv()
    {
        try {
            $result = $this->exportService->exportProductsToCsv();

            if ($result['success']) {
                return response()->json([
                    'message' => 'Xuất CSV thành công',
                    'filename' => $result['filename'],
                    'total_products' => $result['total_products'],
                    'download_url' => url('api/export/download-csv/' . $result['filename'])
                ], 200);
            } else {
                return response()->json([
                    'message' => $result['message']
                ], 500);
            }
        } catch(\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Download file CSV
     *
     * @param string $filename
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
     */
    public function downloadCsv($filename)
    {
        try {
            $result = $this->exportService->downloadCsv($filename);

            if (!$result['success']) {
                return response()->json(['message' => $result['message']], 404);
            }

            return response()->download($result['filepath'], $filename, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);
        } catch(\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
