<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $primaryKey = 'supp_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address'
    ];

    public function stockReceipts (){
        return $this->hasMany(StockReceipt::class, 'supplier_id', 'supp_id');
    }
}
