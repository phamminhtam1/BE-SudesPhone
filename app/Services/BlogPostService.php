<?php

namespace App\Services;

use App\Models\BlogPost;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BlogPostService
{
    public function createBlogPost( array $data, $file, $author_emp_id)
    {
        try{
            DB::beginTransaction();
            $blogPost = new BlogPost();
            $blogPost->author_emp_id = $author_emp_id;
            $blogPost->category_blog_id = $data['category_blog_id'];
            $blogPost->title = $data['title'];
            $blogPost->summary = $data['summary'];
            $blogPost->content = $data['content'];
            $blogPost->meta_title = $data['meta_title'];
            $blogPost->meta_description = $data['meta_description'];
            $blogPost->keywords = $data['keywords'];
            $blogPost->published = $data['published'];
            if (!empty($data['published_at'])) {
                $publishedAt = \DateTime::createFromFormat('Y-m-d\TH:i', $data['published_at']);
                if ($publishedAt) {
                    $blogPost->published_at = $publishedAt->format('Y-m-d H:i:s');
                } else {
                    $blogPost->published_at = null;
                }
            } else {
                $blogPost->published_at = null;
            }
            if($file && $file->isValid()){
                $path = $file->store("blog_posts", 's3');
                Storage::disk('s3')->setVisibility($path, 'public');
                $blogPost->thumbnail_url = $this->getCloudFrontUrl($path);
            }
            $blogPost->save();
            DB::commit();
            return $blogPost;
        }catch(\Exception $e){
            DB::rollBack();
            Log::error('Error creating blog post: ' . $e->getMessage());
            throw new \Exception('Error creating blog post');
        }
    }

    public function getAllBlogPost()
    {
        return BlogPost::orderBy('created_at', 'desc')->with('user.role', 'categoryBlog')->get();
    }

    private function getCloudFrontUrl($path)
    {
        $cloudfront = rtrim(env('CLOUDFRONT_URL'), '/');
        $path = ltrim($path, '/');
        return $cloudfront . '/' . $path;
    }
}
