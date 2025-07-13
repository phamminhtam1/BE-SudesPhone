<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $table = 'addresses';
    protected $fillable = [
        'cust_id',
        'label',
        'line1',
        'line2',
        'city',
        'region',
        'postal_code',
        'country',
        'is_default'
    ];

    public function customer(){
        return $this->belongsTo(Customer::class, 'cust_id', 'cust_id');
    }
}
