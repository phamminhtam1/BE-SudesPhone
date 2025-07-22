<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'prod_id',
        'qty',
        'unit_price',
    ];

    public function order(){
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }
    public function product(){
        return $this->belongsTo(Product::class, 'prod_id', 'prod_id');
    }
}
