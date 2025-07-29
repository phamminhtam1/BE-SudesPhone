<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UpdateExpiredPaymentStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Bắt đầu kiểm tra và cập nhật trạng thái thanh toán hết hạn...');

            // Tìm các đơn hàng có expires_at đã qua thời gian hiện tại
            $expiredOrders = Order::whereHas('payment', function ($query) {
                $query->where('method', 'momo')
                      ->where('pay_status', 'pending');
            })
            ->where('order_status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', Carbon::now())
            ->get();

            $updatedCount = 0;

            foreach ($expiredOrders as $order) {
                try {
                    // Cập nhật trạng thái đơn hàng thành cancelled
                    $order->update([
                        'order_status' => 'cancelled'
                    ]);

                    // Cập nhật trạng thái payment thành failed
                    $order->payment()->update([
                        'pay_status' => 'failed',
                        'pay_at' => now()
                    ]);

                    $updatedCount++;

                    Log::info("Đã cập nhật trạng thái đơn hàng hết hạn: Order ID {$order->order_id}, expires_at: {$order->expires_at}");

                } catch (\Exception $e) {
                    Log::error("Lỗi khi cập nhật đơn hàng {$order->order_id}: " . $e->getMessage());
                }
            }

            if ($updatedCount > 0) {
                Log::info("Đã cập nhật trạng thái {$updatedCount} đơn hàng hết hạn thanh toán");
            } else {
                Log::info("Không có đơn hàng nào cần cập nhật trạng thái");
            }

        } catch (\Exception $e) {
            Log::error("Lỗi trong job UpdateExpiredPaymentStatus: " . $e->getMessage());
        }
    }
}
