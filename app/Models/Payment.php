<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $primaryKey = 'pay_id';
    public $incrementing = true;
    protected $keyType = "int";
    protected $fillable = [
        'order_id',
        'method',
        'transaction_id',
        'pay_at'
    ];

    public function order(){
        return $this->hasOne(Order::class, 'order_id', 'order_id');
    }

}
