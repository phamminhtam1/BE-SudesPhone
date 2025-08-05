<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\BlogPostService;
use App\Http\Requests\BlogPost\CreateBlogPostRequest;

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
        $user = Auth::guard('sanctum')->user();
        if(!$user){
            return response()->json(['message' => 'Không có quyền truy cập'], 401);
        }
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
}
