<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $primaryKey = "order_id";
    public $incrementing = true;
    protected $keyType = "int";
    protected $fillable = [
        'cust_id',
        'branch_id',
        'order_status',
        'payment_status',
        'sub_total',
        'shipping_fee',
        'discount',
        'total_amount',
        'address_customer',
    ];

    public function customer(){
        return $this->belongsTo(Customer::class, 'cust_id', 'cust_id');
    }
    public function branch(){
        return $this->belongsTo(Branch::class, 'branch_id', 'branch_id');
    }
    public function items(){
        return $this->hasMany(OrderItem::class, 'order_id', 'order_id');
    }

}
