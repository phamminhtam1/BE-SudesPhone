<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    protected $primaryKey = 'post_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'author_emp_id',
        'category_blog_id',
        'title',
        'slug',
        'thumbnail_url',
        'summary',
        'content',
        'meta_title',
        'meta_description',
        'keywords',
        'view_count',
        'published',
        'published_at',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'author_emp_id', 'id');
    }

    public function categoryBlog(){
        return $this->belongsTo(CategoryBlogPost::class, 'category_blog_id', 'id');
    }
    protected static function booted()
    {
        static::creating(function (BlogPost $post) {
            if (empty($post->slug)) {
                $post->slug = self::generateUniqueSlug($post->title);
            }
        });

        static::updating(function (BlogPost $post) {
            if (empty($post->slug)) {
                $post->slug = self::generateUniqueSlug($post->title, $post->post_id);
            }
        });
    }

    protected static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (self::where('slug', $slug)
            ->when($ignoreId, fn($query) => $query->where('post_id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $originalSlug . '-' . $counter++;
        }

        return $slug;
    }
}
