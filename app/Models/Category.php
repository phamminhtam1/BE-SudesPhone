<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'meta_title',
        'meta_description',
        'keywords',
        'image',
        'banner',
    ];

    protected static function booted(){
        static::creating(function(Category $category){
            if(empty($category->slug)){
                $category->slug = self::generateUniqueSlug($category->name);
            }
        });
        static::updating(function(Category $category){
            if($category->isDirty('name')){
                $category->slug = self::generateUniqueSlug($category->name);
            }
        });
    }

    protected static function generateUniqueSlug(string $name, $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $i = 1;
        while (
            self::where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $originalSlug . '-' . $i++;
        }
        return $slug;
    }

    public function parent(){
        return $this->belongsTo(Category::class,'parent_id');
    }
    public function children(){
        return $this->hasMany(Category::class,'parent_id')->with('children');
    }
}
