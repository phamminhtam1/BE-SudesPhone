<?php

namespace App\Services;

use App\Models\CategoryBlogPost;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CategoryBlogPostService
{
    public function createCategoryBlogPost( array $data)
    {
        try{
            DB::beginTransaction();
            $blogPost = new CategoryBlogPost();
            $blogPost->name = $data['name'];
            $blogPost->save();
            DB::commit();
            return $blogPost;
        }catch(\Exception $e){
            DB::rollBack();
            Log::error('Error creating category blog post: ' . $e->getMessage());
            throw new \Exception('Error creating category blog post');
        }
    }

    public function getAllCategoryBlogPost()
    {
        return CategoryBlogPost::orderBy('created_at', 'desc')->get();
    }

    public function updateStatusCategoryBlogPost($categoryBlogPost, $status)
    {
        try{
            $categoryBlogPost->status = $status;
            $categoryBlogPost->save();
            return $categoryBlogPost;
        }catch(\Exception $e){
            Log::error('Error updating category blog post status: ' . $e->getMessage());
            throw new \Exception('Error updating category blog post status');
        }
    }
}
