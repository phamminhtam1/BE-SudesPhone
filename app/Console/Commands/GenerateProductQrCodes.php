<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GenerateProductQrCodes extends Command
{
    protected $signature = 'product:generate-qr';

    protected $description = 'Tạo mã QR cho các sản phẩm chưa có qr_path';

    public function handle()
    {
        $products = Product::whereNull('qr_path')->whereNotNull('sku')->get();
        $bar = $this->output->createProgressBar(count($products));

        $this->info("Đang tạo mã QR cho " . count($products) . " sản phẩm...");

        foreach ($products as $product) {
            try {
                $qrCode = QrCode::format('png')->size(300)->generate($product->sku);
                $qrPath = "products/{$product->prod_id}/qr_{$product->sku}.png";

                Storage::disk('s3')->put($qrPath, $qrCode);
                Storage::disk('s3')->setVisibility($qrPath, 'public');

                $product->qr_path = Storage::disk('s3')->url($qrPath);
                $product->save();

                $bar->advance();
            } catch (\Exception $e) {
                $this->error("Lỗi tại sản phẩm ID {$product->prod_id}: " . $e->getMessage());
            }
        }

        $bar->finish();
        $this->info("\n✅ Hoàn tất tạo mã QR.");
        return 0;
    }
}
