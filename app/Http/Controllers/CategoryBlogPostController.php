<?php

namespace App\Http\Controllers;

use App\Models\CategoryBlogPost;
use Illuminate\Http\Request;
use App\Services\CategoryBlogPostService;

class CategoryBlogPostController extends Controller
{
    protected $categoryBlogPostService;
    public function __construct(CategoryBlogPostService $categoryBlogPostService){
        $this->categoryBlogPostService = $categoryBlogPostService;
    }

    public function createCategoryBlogPost(Request $request){
        $data = $request->all();
        try{
            $categoryBlogPost = $this->categoryBlogPostService->createCategoryBlogPost($data);
            return response()->json([
                'message' => 'success',
                    'categoryBlogPost' => $categoryBlogPost,
                ], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAllCategoryBlogPost(){
        try{
            $categoryBlogPosts = $this->categoryBlogPostService->getAllCategoryBlogPost();
            return response()->json([
                'message' => 'success',
                'categoryBlogPosts' => $categoryBlogPosts,
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAllCategoryBlogPostForFE(){
        try{
            $categoryBlogPosts = $this->categoryBlogPostService->getAllCategoryBlogPostForFE();
            return response()->json([
                'message' => 'success',
                'categoryBlogPosts' => $categoryBlogPosts,
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateStatusCategoryBlogPost(CategoryBlogPost $categoryBlogPost, Request $request){
        try{
            $status = $request->status;
            $categoryBlogPost = $this->categoryBlogPostService->updateStatusCategoryBlogPost($categoryBlogPost, $status);
            return response()->json([
                'message' => 'success',
                'categoryBlogPost' => $categoryBlogPost,
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
