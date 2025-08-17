<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ExportService;

class ExportProductsToCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:export-csv';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Xuất danh sách sản phẩm ra file CSV với các cột: ID, Tên sản phẩm, Hình ảnh';

    protected $exportService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ExportService $exportService)
    {
        parent::__construct();
        $this->exportService = $exportService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Bắt đầu xuất dữ liệu sản phẩm ra CSV...');

        try {
            $result = $this->exportService->exportProductsToCsv();

            if ($result['success']) {
                $this->info('✅ Xuất CSV thành công!');
                $this->info('📁 File: ' . $result['filename']);
                $this->info('📊 Tổng số sản phẩm: ' . $result['total_products']);
                $this->info('📍 Đường dẫn: ' . $result['filepath']);

                return 0;
            } else {
                $this->error('❌ Lỗi: ' . $result['message']);
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Có lỗi xảy ra: ' . $e->getMessage());
            return 1;
        }
    }
}
