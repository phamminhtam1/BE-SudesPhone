<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CategoryBlogPost extends Model
{
    protected $table = 'categories_blog';
    protected $fillable = ['name', 'slug'];

    public function blogPosts(): HasMany
    {
        return $this->hasMany(BlogPost::class, 'category_blog_id', 'id');
    }

    protected static function booted()
    {
        static::creating(function (CategoryBlogPost $category) {
            if (empty($category->slug)) {
                $category->slug = self::generateUniqueSlug($category->name);
            }
        });

        static::updating(function (CategoryBlogPost $category) {
            if (empty($category->slug)) {
                $category->slug = self::generateUniqueSlug($category->name, $category->id);
            }
        });
    }

    protected static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (self::where('slug', $slug)
            ->when($ignoreId, fn($query) => $query->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $originalSlug . '-' . $counter++;
        }

        return $slug;
    }
}
