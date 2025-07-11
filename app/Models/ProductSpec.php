<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSpec extends Model
{
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = [
        'prod_id',
        'spec_key',
        'spec_value',
    ] ;

    public function product(){
        return $this->belongsTo(Product::class, 'prod_id', 'prod_id');
    }
}
