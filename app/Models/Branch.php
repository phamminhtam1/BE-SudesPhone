<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Branch extends Model
{
    /**
     * Khóa chính là branch_id (auto‐increment integer).
     */
    protected $primaryKey = 'branch_id';
    public $incrementing   = true;
    protected $keyType     = 'int';

    /**
     * Cho phép gán hàng loạt các trường này.
     */
    protected $fillable = [
        'name',
        'slug',
        'phone',
        'email',
        'address_line1',
        'address_line2',
        'city',
        'region',
        'country',
        'latitude',
        'longitude',
        'status',
    ];

    /**
     * Khi model được tạo hoặc cập nhật thì tự sinh slug.
     */
    protected static function booted()
    {
        // Tự sinh slug khi creating
        static::creating(function (Branch $branch) {
            if (empty($branch->slug)) {
                $branch->slug = self::generateUniqueSlug($branch->name);
            }
        });

        // Nếu name thay đổi khi updating => sinh lại slug
        static::updating(function (Branch $branch) {
            if ($branch->isDirty('name')) {
                $branch->slug = self::generateUniqueSlug($branch->name, $branch->branch_id);
            }
        });
    }

    /**
     * Sinh slug (space→'_' lowercase, bỏ dấu) và đảm bảo duy nhất.
     *
     * @param  string      $name
     * @param  int|null    $ignoreId  branch_id cũ khi cập nhật
     * @return string
     */
    protected static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        // Str::slug → remove accent, space→'-', lowercase
        $base = Str::slug($name);
        // đổi '-' → '_'
        $base = str_replace('-', '_', $base);

        $slug = $base;
        $i    = 1;

        // Kiểm tra trùng slug
        $query = static::where('slug', $slug);
        if ($ignoreId) {
            $query->where('branch_id', '!=', $ignoreId);
        }

        // Nếu đã có -> thêm _1, _2…
        while ($query->exists()) {
            $slug  = $base . '_' . $i++;
            $query = static::where('slug', $slug);
            if ($ignoreId) {
                $query->where('branch_id', '!=', $ignoreId);
            }
        }

        return $slug;
    }
    public function images()
    {
        return $this->hasMany(BranchImage::class, 'branch_id', 'branch_id');
    }

    public function users(){
        return $this -> hasMany(User::class, 'branch_id', 'branch_id');
    }

    public function stockReceipts(){
        return $this->hasMany(StockReceipt::class,'branch)id','branch_id');
    }
}
