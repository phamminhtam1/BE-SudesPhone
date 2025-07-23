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
}
