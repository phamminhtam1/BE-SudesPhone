<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $table = 'addresses';
    protected $fillable = [
        'cust_id',
        'label',
        'line',
        'city',
        'region',
        'ward',
        'country',
        'is_default'
    ];

    public function customer(){
        return $this->belongsTo(Customer::class, 'cust_id', 'cust_id');
    }
}
