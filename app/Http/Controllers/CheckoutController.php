<?php

namespace App\Http\Controllers;

use App\Services\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    protected $checkoutService;

    public function __construct(CheckoutService $checkoutService)
    {
        $this->checkoutService = $checkoutService;
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

            // Chuẩn bị dữ liệu đơn hàng
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
                'pay_status' => 'pending',
                'order_items' => $request->order_items,
            ];

            // Xử lý checkout thông qua service
            $result = $this->checkoutService->processCheckout($orderData, $customer->cust_id);
            $order = $result['order'];

            if ($request->payment_method === 'momo') {
                // Chuyển đến thanh toán MoMo
                $paymentUrl = $this->checkoutService->createMomoPayment($order, $request->total_amount);

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
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xử lý đơn hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Callback từ MoMo sau khi thanh toán
     */
    public function momoCallback(Request $request): JsonResponse
    {
        try {
            $result = $this->checkoutService->processMomoCallback($request->all());

            if ($result['success']) {
                return response()->json(['success' => true]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Callback processing error'
            ], 500);
        }
    }

    /**
     * Trang cảm ơn sau khi thanh toán
     */
    public function thankYou(Request $request): JsonResponse
    {
        $orderId = $request->query('orderId');
        $resultCode = $request->query('resultCode');

        if ($orderId) {
            $order = $this->checkoutService->getOrderForThankYou($orderId);

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
}
