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

    public function getAllBlogPostForFE(){
        return BlogPost::where('published', '=', '1')
        ->orderBy('created_at', 'desc')->get();
    }

    private function getCloudFrontUrl($path)
    {
        $cloudfront = rtrim(env('CLOUDFRONT_URL'), '/');
        $path = ltrim($path, '/');
        return $cloudfront . '/' . $path;
    }

    public function getBlogPostById(BlogPost $blogPost){
        return $blogPost->load('user.role', 'categoryBlog');
    }

    public function getBlogPostByIdForFE(BlogPost $blogPost){
        return $blogPost->load('categoryBlog');
    }
    public function getHotBlogPost(){
        return BlogPost::where('published', '=','1')
            ->orderByDesc('view_count')
            ->take(5)
            ->get();
    }

    public function getBlogPostByCategoryId($categoryId){
        return BlogPost::where('category_blog_id', '=', $categoryId)
            ->where('published', '=', '1')
            ->orderBy('created_at', 'desc')
            ->with('categoryBlog:id,name')
            ->get();
    }

    public function updateBlogPost(BlogPost $blogPost, array $data, $file) {
        try{
            DB::beginTransaction();
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

            // Chỉ xử lý ảnh nếu có file mới được upload
            if($file && $file->isValid()){
                // Xóa ảnh cũ nếu có
                $this->deleteOldThumbnail($blogPost->thumbnail_url);

                // Upload ảnh mới
                $path = $file->store("blog_posts", 's3');
                Storage::disk('s3')->setVisibility($path, 'public');
                $blogPost->thumbnail_url = $this->getCloudFrontUrl($path);
            }
            // Nếu không có file mới, giữ nguyên thumbnail_url hiện tại

            $blogPost->save();
            DB::commit();
            return $blogPost;
        }catch(\Exception $e){
            DB::rollBack();
            Log::error('Error updating blog post: ' . $e->getMessage());
            throw new \Exception('Error updating blog post');
        }
    }

    public function deleteBlogPost(BlogPost $blogPost){
        try{
            DB::beginTransaction();
            // Xóa thumbnail trên S3 nếu có
            $this->deleteOldThumbnail($blogPost->thumbnail_url);
            $blogPost->delete();
            DB::commit();
            return true;
        }catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function deleteOldThumbnail($thumbnailUrl)
    {
        if (!$thumbnailUrl) {
            return;
        }

        try {
            $oldPath = ltrim(parse_url($thumbnailUrl, PHP_URL_PATH), '/');
            if (Storage::disk('s3')->exists($oldPath)) {
                Storage::disk('s3')->delete($oldPath);
                Log::info("Đã xóa ảnh cũ: " . $oldPath);
            }
        } catch (\Exception $e) {
            Log::warning("Không thể xóa ảnh cũ: " . $e->getMessage());
        }
    }
}
