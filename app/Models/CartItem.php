<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    public $incrementing = true;
    protected $fillable = [
        'cart_id',
        'prod_id',
        'qty',
    ];

    public function cart(){
        return $this->belongsTo(Cart::class, 'cart_id', 'cart_id');
    }

    public function product(){
        return $this->hasMany(Product::class, 'prod_id', 'prod_id');
    }
}
