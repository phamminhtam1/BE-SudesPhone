<?php

namespace App\Http\Controllers;
use App\Services\OrderService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Requests\Order\CreateOrderRequest;

class OrderController extends Controller
{
    protected $orderService;
    public function __construct(OrderService $orderService){
        $this->orderService = $orderService;
    }

    public function createNewOrder(CreateOrderRequest $request){
        $cust_id = Auth::guard('customer')->user()->cust_id;
        // dd($cust_id);
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
}
