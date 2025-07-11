<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $primaryKey = 'img_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'prod_id',
        'img_url',
        'alt_text',
        'sort_order',
    ];

    public function product(){
        return $this->belongsTo(Product::class, 'prod_id', 'prod_id');
    }
}
