<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\UpdateExpiredPaymentStatus;

class UpdateExpiredPaymentStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:update-expired-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cập nhật trạng thái thanh toán cho các đơn hàng đã hết hạn';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Đang kiểm tra và cập nhật trạng thái thanh toán hết hạn...');

        UpdateExpiredPaymentStatus::dispatch();

        $this->info('Job cập nhật trạng thái thanh toán hết hạn đã được đưa vào queue.');

        return Command::SUCCESS;
    }
}
