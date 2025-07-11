<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $primaryKey = "prod_id";
    public $incrementing = true;
    protected $keyType = "int";

    protected $fillable = [
        'cat_id',
        'name',
        'slug',
        'sku',
        'short_desc',
        'long_desc',
        'price',
        'discount_price',
        'warranty_months',
        'stock_qty',
        'status',
        'keywords'
    ];

    protected static function booted()
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = self::generateUniqueSlug($product->name);
            }

            if (empty($product->sku)) {
                $nextId = Product::max('prod_id') + 1;
                $product->sku = self::generateSKU($product->name, $nextId);
            }
        });

        static::updating(function (Product $product) {
            if ($product->isDirty('name')) {
                $product->slug = self::generateUniqueSlug($product->name, $product->prod_id);
            }
        });

        static::creating(function (Product $product) {
            if (is_null($product->stock_qty)) {
                $product->stock_qty = 0;
            }
        });
    }

    protected static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $base = str_replace('-', '_', $base);
        $slug = $base;
        $i = 1;

        $query = static::where('slug', $slug);
        if ($ignoreId) {
            $query->where('prod_id', '!=', $ignoreId);
        }

        while ($query->exists()) {
            $slug = $base . '_' . $i++;
            $query = static::where('slug', $slug);
            if ($ignoreId) {
                $query->where('prod_id', '!=', $ignoreId);
            }
        }

        return $slug;
    }


    protected static function generateSKU(string $name, int $nextId): string
    {
        $prefix = strtoupper(Str::slug(Str::words($name, 2, ''), '-')); // AO-THUN
        return $prefix . '-' . str_pad($nextId, 6, '0', STR_PAD_LEFT); // AO-THUN-000123
    }

    public function category(){
        return $this->belongsTo(Category::class, 'cat_id', 'id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'prod_id', 'prod_id');
    }

    public function specs(){
        return $this->hasMany(ProductSpec::class,'prod_id','prod_id');
    }
    public function stockReceipts() {
        return $this->hasMany(StockReceipt::class, 'product_id', 'prod_id');
    }
}
