<?php

namespace App\Http\Controllers;
use App\Services\OrderService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Requests\Order\CreateOrderRequest;
use App\Models\Order;

class OrderController extends Controller
{
    protected $orderService;
    public function __construct(OrderService $orderService){
        $this->orderService = $orderService;
    }

    public function createNewOrder(CreateOrderRequest $request){
        $cust_id = Auth::guard('customer')->user()->cust_id;
        try{
            $data = $request->validated();
            $order = $this->orderService->createOrder($data, $cust_id);
            return response()->json([
                'message'=>'succes',
                'order'=>$order
            ],200);
        }catch(\Exception $e){
            return response()->json(['message'=>$e->getMessage()]);
        }
    }

    public function getMyListOrder(){
        $customer = Auth::guard('customer')->user();
        if(!$customer) {
            return response()->json(['message'=>'Không xác thực'],401);
        }
        $cust_id = $customer->cust_id;
        $order = $this->orderService->getMyListOrder($cust_id);
        return response()->json([
            'message'=>'success',
            'orderlist'=>$order
        ],200);
    }

    public function getOrderDetail(Order $order){
        $customer = Auth::guard('customer')->user();
        if(!$customer){
            return response()->json(['message'=>'Không xác thực'],401);
        }
        $cust_id = $customer->cust_id;
        $order = $this->orderService->getOrderDetail($order, $cust_id);
        return response()->json([
            'message'=>'success',
            'order'=>$order
        ],200);
    }

    public function getOrderDetailForAdmin(Order $order){
        $admin = Auth::guard('sanctum')->user();
        if(!$admin){
            return response()->json(['message'=>'Không xác thực'],401);
        }
        if(!in_array($admin->role_id, [1,2])){
            return response()->json(['message'=>'Bạn không có quyền truy cập'],403);
        }
        $order = $this->orderService->getOrderDetailForAdmin($order);
        return response()->json([
            'message'=>'success',
            'order'=>$order
        ],200);
    }
    public function getAllOrder(){
        $admin = Auth::guard('sanctum')->user();
        if(!$admin){
            return response()->json(['message'=>'Không xác thực'],401);
        }
        if(!in_array($admin->role_id, [1,2])){
            return response()->json(['message'=>'Bạn không có quyền truy cập'],403);
        }
        $order = $this->orderService->getAllOrder();
        return response()->json([
            'message'=>'success',
            'order'=>$order
        ],200);
    }

    public function gettTotalProfit(){
        $admin = Auth::guard('sanctum')->user();
        if(!$admin){
            return response()->json(['message'=>'Không xác thực'],401);
        }
        if(!in_array($admin->role_id, [1,2])){
            return response()->json(['message'=>'Bạn không có quyền truy cập'],403);
        }
        $total_profit = $this->orderService->getTotalProfit();
        $total_order = $this->orderService->getTotalOrder();
        $total_order_waiting = $this->orderService->getTotalOrderWaiting();
        $total_order_completed = $this->orderService->getTotalOrderCompleted();
        return response()->json([
            'message'=>'success',
            'total_profit'=>$total_profit,
            'total_order'=>$total_order,
            'total_order_waiting'=>$total_order_waiting,
            'total_order_completed'=>$total_order_completed
        ],200);
    }
}
