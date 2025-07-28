<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Customer;
use App\Services\OrderService;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    protected $orderService;
    protected $cartService;

    public function __construct(OrderService $orderService, CartService $cartService)
    {
        $this->orderService = $orderService;
        $this->cartService = $cartService;
    }

    /**
     * Tạo đơn hàng và chuyển đến thanh toán MoMo
     */
    public function processCheckout(Request $request): JsonResponse
    {
        try {
            // Validate request
            $request->validate([
                'name' => 'required|string|min:2|max:100',
                'phone' => 'required|string',
                'address_customer' => 'required|string',
                'shipping_fee' => 'required|numeric|min:0',
                'payment_method' => 'required|in:cod,momo,bank',
                'sub_total' => 'required|numeric|min:0',
                'total_amount' => 'required|numeric|min:0',
                'order_items' => 'required|array|min:1',
                'order_items.*.prod_id' => 'required|integer|exists:products,prod_id',
                'order_items.*.qty' => 'required|integer|min:1',
                'order_items.*.unit_price' => 'required|numeric|min:0',
            ]);

            // Lấy thông tin customer từ token
            $customer = Auth::guard('customer')->user();
            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin khách hàng'
                ], 401);
            }

            // Tạo đơn hàng
            $orderData = [
                'order_status' => 'pending',
                'name' => $request->name,
                'phone' => $request->phone,
                'sub_total' => $request->sub_total,
                'shipping_fee' => $request->shipping_fee,
                'discount' => $request->discount ?? 0,
                'total_amount' => $request->total_amount,
                'address_customer' => $request->address_customer,
                'method_payment' => $request->payment_method,
                'pay_status' => 'pending',  // ✅ Chỉ cho bảng payments
                'order_items' => $request->order_items,
            ];

            // Tạo đơn hàng trong database
            $order = $this->orderService->createOrder($orderData, $customer->cust_id);

            // Xóa cart sau khi tạo đơn hàng thành công
            $this->cartService->clearCart($customer->cust_id);

            if ($request->payment_method === 'momo') {
                // Chuyển đến thanh toán MoMo
                $paymentUrl = $this->createMomoPayment($order, $request->total_amount);

                return response()->json([
                    'success' => true,
                    'message' => 'Đơn hàng đã được tạo thành công',
                    'data' => [
                        'order_id' => $order->order_id,
                        'payment_url' => $paymentUrl,
                        'total_amount' => $request->total_amount
                    ]
                ]);
            } else {
                // Thanh toán COD hoặc bank transfer
                return response()->json([
                    'success' => true,
                    'message' => 'Đơn hàng đã được tạo thành công',
                    'data' => [
                        'order_id' => $order->order_id,
                        'total_amount' => $request->total_amount,
                        'payment_method' => $request->payment_method
                    ]
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Checkout error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xử lý đơn hàng'
            ], 500);
        }
    }

    /**
     * Tạo thanh toán MoMo
     */
    private function createMomoPayment(Order $order, float $amount): string
    {
        $endpoint = config('payment.momo.endpoint', 'https://test-payment.momo.vn/v2/gateway/api/create');
        $partnerCode = config('payment.momo.partner_code', 'MOMOBKUN20180529');
        $accessKey = config('payment.momo.access_key', 'klm05TvNBzhg7h7j');
        $secretKey = config('payment.momo.secret_key', 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa');

        $orderInfo = "Thanh toán đơn hàng #" . $order->order_id;
        $orderId = $order->order_id . "_" . time();
        $redirectUrl = "http://localhost:5173/checkout/thankyou";
        // ⚠️ QUAN TRỌNG: Thay đổi localhost thành ngrok URL hoặc domain thật
        // Ví dụ: $ipnUrl = "https://abc123.ngrok.io/api/checkout/momo-callback";
        $ipnUrl = config('app.url') . "/api/checkout/momo-callback";
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
     * Callback từ MoMo sau khi thanh toán
     */
    public function momoCallback(Request $request): JsonResponse
    {
        try {
            Log::info('MoMo callback received', $request->all());

            $orderId = $request->input('orderId');
            $resultCode = $request->input('resultCode');
            $message = $request->input('message');
            $transId = $request->input('transId');
            $amount = $request->input('amount');

            // Tách order_id từ orderId (format: order_id_timestamp)
            $orderIdParts = explode('_', $orderId);
            $actualOrderId = $orderIdParts[0];

            $order = Order::find($actualOrderId);
            if (!$order) {
                Log::error('Order not found: ' . $actualOrderId);
                return response()->json(['success' => false, 'message' => 'Order not found'], 404);
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
                $order->update([
                    'order_status' => 'paid'  // ✅ Chỉ update order_status
                ]);

                // Cập nhật payment record
                Payment::where('order_id', $order->order_id)
                    ->where('method', 'momo')
                    ->update([
                        'pay_status' => 'success',  // ✅ Update pay_status trong bảng payments
                        'transaction_id' => $transId,
                        'pay_at' => now(),
                    ]);

                // Xóa cart sau khi thanh toán thành công
                $this->cartService->clearCart($order->cust_id);

                Log::info('Payment successful for order: ' . $order->order_id);
            } else {
                // Thanh toán thất bại
                $order->update([
                    'order_status' => 'cancelled'  // ✅ Chỉ update order_status
                ]);

                // Cập nhật payment record cho trường hợp thất bại
                Payment::where('order_id', $order->order_id)
                    ->where('method', 'momo')
                    ->update([
                        'pay_status' => 'failed',  // ✅ Update pay_status trong bảng payments
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

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('MoMo callback error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Callback processing error'], 500);
        }
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
     * Trang cảm ơn sau khi thanh toán
     */
    public function thankYou(Request $request): JsonResponse
    {
        $orderId = $request->query('orderId');
        $resultCode = $request->query('resultCode');

        if ($orderId) {
            $orderIdParts = explode('_', $orderId);
            $actualOrderId = $orderIdParts[0];

            // Lấy thông tin đơn hàng đầy đủ
            $order = Order::with([
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

            if ($order) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'order' => $order,
                        'payment_status' => $resultCode == 0 ? 'success' : 'failed',
                        'result_code' => $resultCode
                    ]
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy thông tin đơn hàng'
        ], 404);
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
