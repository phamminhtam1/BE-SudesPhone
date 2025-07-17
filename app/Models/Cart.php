<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Cart extends Model
{
    protected $primaryKey = 'cart_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'cust_id'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->cart_id)) {
                $model->cart_id = (string) Str::uuid();
            }
        });
    }

    public function items(){
        return $this->hasMany(CartItem::class, 'cart_id', 'cart_id');
    }

    public function customer(){
        return $this->belongsTo(Customer::class, 'cust_id', 'cust_id');
    }
}
