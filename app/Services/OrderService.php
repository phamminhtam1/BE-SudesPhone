<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Payment;
use App\Models\CartItem;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function createOrder(array $data, $cust_id){
        try{
            DB::beginTransaction();
            $order = new Order();
            $order->cust_id = $cust_id;
            $order->order_status = $data['order_status'];
            $order->name = $data['name'];
            $order->phone = $data['phone'];
            $order->sub_total = $data['sub_total'];
            $order->shipping_fee = $data['shipping_fee'];
            $order->discount = $data['discount'] ??null;
            $order->total_amount = $data['total_amount'];
            $order->address_customer = $data['address_customer'];
            $order->save();
            $payment = new Payment();
            $payment->order_id = $order->order_id;
            $payment->method = $data['method_payment'];
            $payment->pay_status = $data['pay_status'];
            $payment->transaction_id = $data['transaction_id']??null;
            $payment->pay_at = now();

            $payment->save();
            foreach($data['order_items'] as $items){
                $order_item = new OrderItem();
                $order_item->order_id = $order->order_id;
                $order_item->prod_id = $items['prod_id'];
                $order_item->qty = $items['qty'];
                $order_item->unit_price = $items['unit_price'];
                $order_item->save();
            }
            DB::commit();
            return $order->load('items', 'payment');
        }catch(\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function getMyListOrder($cust_id){
        $order = Order::where('cust_id', $cust_id)->with('payment')->get();
        return $order;
    }

    public function getOrderDetail($order, $cust_id){
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
            }
        ])  ->where('order_id', $order->order_id)
            ->where('cust_id', $cust_id)
            ->first();
        return $order;
    }

    public function getOrderDetailForAdmin($order){
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
            }
        ])  ->where('order_id', $order->order_id)
            ->first();
        return $order;
    }

    public function getAllOrder(){
        $order = Order::with([
            'customer' => function($query){
                $query->select('cust_id', 'first_name', 'last_name', 'phone', 'email');
            },
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
            }
        ])  ->get();
        return $order;
    }
    public function getTotalProfit(){
        $total_profit = Order::where('order_status', 'completed')
        ->where('payment_status', 'paid')
        ->sum('total_amount');
        return $total_profit;
    }

    public function getTotalOrder(){
        $total_order = Order::count();
        return $total_order;
    }

    public function getTotalOrderWaiting(){
        $total_order_waiting = Order::where('order_status', 'pending')
        ->count();
        return $total_order_waiting;
    }

    public function getTotalOrderCompleted(){
        $total_order_completed = Order::where('order_status', 'completed')
        ->count();
        return $total_order_completed;
    }

    public function changeOrderStatus(Order $order, string $status){
        try {
            $old_status = $order->order_status;
            if($old_status == 'paid'){
                if($status == 'pending'){
                    throw new \Exception('Không thể chuyển đơn hàng đã thanh toán sang trạng thái chờ xử lý', 400);
                }
            }
            if($old_status == 'shipped'){
                if($status == 'pending'){
                    throw new \Exception('Không thể chuyển đơn hàng đã được giao sang trạng thái chờ xử lý', 400);
                }
                if($status == 'paid'){
                    throw new \Exception('Không thể chuyển đơn hàng đã được giao sang trạng thái đã xử lý', 400);
                }
            }
            if($old_status == 'completed'){
                if($status == 'pending'){
                    throw new \Exception('Không thể chuyển đơn hàng đã được giao sang trạng thái chờ xử lý', 400);
                }
                if($status == 'paid'){
                    throw new \Exception('Không thể chuyển đơn hàng đã được giao sang trạng thái đã xử lý', 400);
                }
                if($status == 'shipped'){
                    throw new \Exception('Không thể chuyển đơn hàng đã được giao sang trạng thái đã giao', 400);
                }
            }
            if($status == 'completed'){
                foreach($order->items as $item){
                    $product = Product::where('prod_id', $item->prod_id)->first();
                    if($product){
                        $product->stock_qty = $product->stock_qty - $item->qty;
                        $product->save();
                    }
                }
            }
            $order->order_status = $status;
            $order->save();
            return $order;
        } catch (\Exception $e) {
            throw $e;
        }
    }


}
