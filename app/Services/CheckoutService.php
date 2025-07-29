<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Jobs\CancelExpiredOrders;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CheckoutService
{
    protected $orderService;
    protected $cartService;

    public function __construct(OrderService $orderService, CartService $cartService)
    {
        $this->orderService = $orderService;
        $this->cartService = $cartService;
    }

    /**
     * Xử lý checkout và tạo đơn hàng
     */
    public function processCheckout(array $data, $customerId): array
    {
        try {
            DB::beginTransaction();

            // Tạo đơn hàng
            $order = $this->orderService->createOrder($data, $customerId);

            DB::commit();

            return [
                'success' => true,
                'order' => $order
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Tạo thanh toán MoMo
     */
    public function createMomoPayment(Order $order, float $amount): string
    {
        $endpoint = config('payment.momo.endpoint', 'https://test-payment.momo.vn/v2/gateway/api/create');
        $partnerCode = config('payment.momo.partner_code', 'MOMOBKUN20180529');
        $accessKey = config('payment.momo.access_key', 'klm05TvNBzhg7h7j');
        $secretKey = config('payment.momo.secret_key', 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa');

        $orderInfo = "SUDESPHONE #" . $order->order_id;
        $orderId = $order->order_id . "_" . time();
        $redirectUrl = "http://localhost:5173/checkout/thankyou";

        // Sử dụng ngrok URL để MoMo có thể gọi callback
        $ipnUrl = "https://7a8d287d97b0.ngrok-free.app/api/checkout/momo-callback";

        // Log IPN URL để debug
        Log::info('MoMo IPN URL: ' . $ipnUrl);

        $extraData = "";
        $requestId = time() . "";
        $requestType = "payWithATM";

        // Tạo signature
        $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&ipnUrl=" . $ipnUrl . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&partnerCode=" . $partnerCode . "&redirectUrl=" . $redirectUrl . "&requestId=" . $requestId . "&requestType=" . $requestType;
        $signature = hash_hmac("sha256", $rawHash, $secretKey);

        $data = [
            'partnerCode' => $partnerCode,
            'partnerName' => config('app.name', 'Test Store'),
            "storeId" => "MomoTestStore",
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'lang' => 'vi',
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature
        ];

        $result = $this->execPostRequest($endpoint, json_encode($data));
        $jsonResult = json_decode($result, true);

        if (isset($jsonResult['payUrl'])) {
            // Lưu thông tin payment
            Payment::create([
                'order_id' => $order->order_id,
                'method' => 'momo',
                'transaction_id' => $orderId,
                'pay_at' => now(),
            ]);

            return $jsonResult['payUrl'];
        }

        throw new \Exception('Không thể tạo URL thanh toán MoMo');
    }

    /**
     * Xử lý callback từ MoMo
     */
    public function processMomoCallback(array $callbackData): array
    {
        try {
            Log::info('MoMo callback received', $callbackData);

            $orderId = $callbackData['orderId'] ?? null;
            $resultCode = $callbackData['resultCode'] ?? null;
            $message = $callbackData['message'] ?? null;
            $transId = $callbackData['transId'] ?? null;
            $amount = $callbackData['amount'] ?? null;

            if (!$orderId) {
                throw new \Exception('Missing orderId in callback');
            }

            // Tách order_id từ orderId (format: order_id_timestamp)
            $orderIdParts = explode('_', $orderId);
            $actualOrderId = $orderIdParts[0];

            $order = Order::find($actualOrderId);
            if (!$order) {
                Log::error('Order not found: ' . $actualOrderId);
                return [
                    'success' => false,
                    'message' => 'Order not found'
                ];
            }

            // Log chi tiết về giao dịch
            Log::info('MoMo transaction details', [
                'order_id' => $actualOrderId,
                'result_code' => $resultCode,
                'message' => $message,
                'trans_id' => $transId,
                'amount' => $amount
            ]);

            if ($resultCode == 0) {
                // Thanh toán thành công
                $this->handleSuccessfulPayment($order, $transId);
                Log::info('Payment successful for order: ' . $order->order_id);
            } else {
                // Thanh toán thất bại
                $this->handleFailedPayment($order, $transId, $resultCode, $message);
            }

            return ['success' => true];

        } catch (\Exception $e) {
            Log::error('MoMo callback error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Callback processing error'
            ];
        }
    }

    /**
     * Xử lý thanh toán thành công
     */
    private function handleSuccessfulPayment(Order $order, string $transId): void
    {
        // Cập nhật payment record
        Payment::where('order_id', $order->order_id)
            ->where('method', 'momo')
            ->update([
                'pay_status' => 'success',
                'transaction_id' => $transId,
                'pay_at' => now(),
            ]);

        // Cập nhật trạng thái đơn hàng thành paid
        $order->update([
            'order_status' => 'paid'
        ]);
    }

    /**
     * Xử lý thanh toán thất bại
     */
    private function handleFailedPayment(Order $order, string $transId, string $resultCode, string $message): void
    {
        // Thanh toán thất bại
        $order->update([
            'order_status' => 'cancelled'
        ]);

        // Cập nhật payment record cho trường hợp thất bại
        Payment::where('order_id', $order->order_id)
            ->where('method', 'momo')
            ->update([
                'pay_status' => 'failed',
                'transaction_id' => $transId,
                'pay_at' => now(),
            ]);

        // Log chi tiết lỗi
        Log::warning('Payment failed for order: ' . $order->order_id, [
            'result_code' => $resultCode,
            'message' => $message,
            'trans_id' => $transId
        ]);

        // Trả về thông báo lỗi cụ thể
        $errorMessage = $this->getMoMoErrorMessage($resultCode, $message);
        Log::error('MoMo payment error: ' . $errorMessage);
    }

    /**
     * Lấy thông báo lỗi MoMo theo resultCode
     */
    private function getMoMoErrorMessage($resultCode, $message): string
    {
        $errorMessages = [
            '1001' => 'Giao dịch bị từ chối do nhà phát hành tài khoản thanh toán',
            '1002' => 'Giao dịch bị từ chối do tài khoản thanh toán không đủ số dư',
            '1003' => 'Giao dịch bị từ chối do thông tin tài khoản thanh toán không hợp lệ',
            '1004' => 'Giao dịch bị từ chối do tài khoản thanh toán bị khóa',
            '1005' => 'Giao dịch bị từ chối do vượt quá hạn mức giao dịch',
            '1006' => 'Giao dịch bị từ chối do thông tin giao dịch không hợp lệ',
            '1007' => 'Giao dịch bị từ chối do hệ thống MoMo đang bảo trì',
            '1008' => 'Giao dịch bị từ chối do timeout',
            '1009' => 'Giao dịch bị từ chối do người dùng hủy',
            '1010' => 'Giao dịch bị từ chối do lỗi hệ thống',
        ];

        return $errorMessages[$resultCode] ?? $message ?? 'Lỗi thanh toán không xác định';
    }

    /**
     * Lấy thông tin đơn hàng cho trang thank you
     */
    public function getOrderForThankYou(string $orderId): ?Order
    {
        $orderIdParts = explode('_', $orderId);
        $actualOrderId = $orderIdParts[0];

        return Order::with([
            'items.product' => function($query){
                $query->select('prod_id', 'name', 'discount_price');
            },
            'items.product.images' => function($query){
                $query->select('img_id', 'prod_id', 'img_url')->orderBy('img_id');
            },
            'items.product.specs' => function($query){
                $query->select('prod_id', 'spec_key', 'spec_value')
                    ->where('spec_key', 'Màu sắc');
            },
            'payment' => function($query){
                $query->select('pay_id', 'order_id', 'method', 'pay_status', 'transaction_id', 'pay_at');
            },
            'customer' => function($query){
                $query->select('cust_id', 'first_name', 'last_name', 'email', 'phone');
            }
        ])->where('order_id', $actualOrderId)->first();
    }

    /**
     * Thực hiện POST request
     */
    private function execPostRequest($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            Log::error('CURL Error: ' . curl_error($ch));
            throw new \Exception('CURL Error: ' . curl_error($ch));
        }

        curl_close($ch);
        return $result;
    }
}
