<?php

namespace App\Services;

use App\Models\Order;
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
            $order->payment_status = $data['payment_status'];
            $order->name = $data['name'];
            $order->phone = $data['phone'];
            $order->sub_total = $data['sub_total'];
            $order->shipping_fee = $data['shipping_fee'];
            $order->discount = $data['discount'] ??null;
            $order->total_amount = $data['total_amount'];
            $order->address_customer = $data['address_customer'];
            $order->save();

            foreach($data['order_items'] as $items){
                $order_item = new OrderItem();
                $order_item->order_id = $order->order_id;
                $order_item->prod_id = $items['prod_id'];
                $order_item->qty = $items['qty'];
                $order_item->unit_price = $items['unit_price'];
                $order_item->save();
            }
            DB::commit();
            return $order->load('items');
        }catch(\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function getMyListOrder($cust_id){
        $order = Order::where('cust_id', $cust_id)->get();
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
            }
        ])  ->where('order_id', $order->order_id)
            ->where('cust_id', $cust_id)
            ->first();
        return $order;
    }
}
