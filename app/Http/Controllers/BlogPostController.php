<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\BlogPostService;
use App\Http\Requests\BlogPost\CreateBlogPostRequest;
use App\Http\Requests\BlogPost\UpdateBlogPostRequest;
use App\Models\BlogPost;

class BlogPostController extends Controller
{
    protected $blogPostService;

    public function __construct(BlogPostService $blogPostService)
    {
        $this->blogPostService = $blogPostService;
    }

    public function addNewBlogPost(CreateBlogPostRequest $request){
        try{
            $user = Auth::guard('sanctum')->user();
            if(!$user){
                return response()->json(['message' => 'Không xác thực'], 401);
            }
            $author_emp_id = $user->id;
            // dd($author_emp_id);
            $data = $request->validated();

            /** @var \Illuminate\Http\Request $request */
            $file = $request->file('thumbnail');

            unset($data['thumbnail']);
            $blogPost = $this->blogPostService->createBlogPost($data, $file, $author_emp_id);
            return response()->json([
                'message' => 'success',
                'blogPost' => $blogPost,
            ], 200);
        }catch(\Exception $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function getAllBlogPost(){
        try{
            $blogPosts = $this->blogPostService->getAllBlogPost();
            return response()->json([
                'message' => 'success',
                'blogPosts' => $blogPosts,
            ], 200);
        }catch(\Exception $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function getAllBlogPostForFE(){
        try{
            $blogPosts = $this->blogPostService->getAllBlogPostForFE();
            return response()->json([
                'message' => 'success',
                'blogPosts' => $blogPosts,
            ], 200);
        }catch(\Exception $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function getHotBlogPost(){
        try{
            $blogPosts = $this->blogPostService->getHotBlogPost();
            return response()->json([
                'message' => 'success',
                'blogPosts' => $blogPosts,
            ], 200);
        }catch(\Exception $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function getBlogPostByCategoryId($categoryId){
        try{
            $blogPosts = $this->blogPostService->getBlogPostByCategoryId($categoryId);
            return response()->json([
                'message' => 'success',
                'blogPosts' => $blogPosts,
            ], 200);
        }catch(\Exception $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function getBlogPostById(BlogPost $blogPost){
        $blogPost = $this->blogPostService->getBlogPostById($blogPost);
        return response()->json([
            'message' => 'success',
            'blogPost' => $blogPost,
        ], 200);
    }

    public function getBlogPostByIdForFE(BlogPost $blogPost){
        if($blogPost->published =='0'){
            return response()->json([
                'message' => 'Bài viết không tồn tại hoặc chưa được xuất bản.'
            ], 404);
        }
        $blogPost->increment('view_count');
        $blogPost = $this->blogPostService->getBlogPostByIdForFE($blogPost);
        return response()->json([
            'message'=> 'success',
            'blogPost' => $blogPost
        ], 200);
    }

    public function editBlogPost(UpdateBlogPostRequest $request, BlogPost $blogPost){
        try{
            $user = Auth::guard('sanctum')->user();
            if(!$user){
                return response()->json(['message' => 'Không xác thực'], 401);
            }
            $author_emp_id = $user->id;
            $data = $request->validated();

            /** @var \Illuminate\Http\Request $request */
            $file = $request->file('thumbnail');
            unset($data['thumbnail']);
            $blogPost = $this->blogPostService->updateBlogPost($blogPost, $data, $file, $author_emp_id);
            return response()->json([
                'message' => 'success',
                'blogPost' => $blogPost,
            ], 200);
        }catch(\Exception $e){
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function deleteBlogPost(BlogPost $blogPost)
    {
        try {
            $this->blogPostService->deleteBlogPost($blogPost);
            return response()->json([
                'message' => 'success'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
