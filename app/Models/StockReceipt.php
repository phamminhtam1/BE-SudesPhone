<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockReceipt extends Model
{
    protected $primaryKey = "receipt_id";
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'receipt_code',
        'branch_id',
        'user_id',
        'supplier_id',
        'product_id',
        'sku',
        'qty',
        'unit_price',
        'total_cost',
        'note',
        'status',
        'approved_by',
        'received_at',
    ];

    protected static function booted()
    {
        static::creating(function (StockReceipt $receipt) {
            $today = now()->format('Ymd');
            $countToday = self::whereDate('created_at', today())->count() + 1;
            $receipt->receipt_code = 'PNK-' . $today . '-' . str_pad($countToday, 5, '0', STR_PAD_LEFT);
        });
    }
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'prod_id');
    }

    public function user(){
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function approver(){
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }

    public function branch(){
        return $this->belongsTo(Branch::class,'branch_id','branch_id');
    }

    public function supplier(){
        return $this->belongsTo(Supplier::class,'supplier_id','supp_id');
    }

}
