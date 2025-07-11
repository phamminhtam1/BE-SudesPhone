<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Services\CategoryService;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService){
        $this->categoryService = $categoryService;
    }

    public function addNewCategory(Request $request){
        $validate = Validator::make($request->all(), [
            'parent_id' => 'nullable|exists:categories,id',
            'name' => 'required|string',
            'meta_title' => 'required|string',
            'meta_description'=> 'required|string',
            'keywords'=> 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'banner' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);
        if($validate->fails()){
            return response()->json($validate->errors(),403);
        }
        try{
            $data = $validate->validated();
            $data['image'] = $request->file('image');
            $data['banner'] = $request->file('banner');
            $category = $this->categoryService->createCategory($data);
            return response()->json([
                'message' => 'success',
                'category' =>$category
            ], 200);
        }catch(\Exception $e){
            return response()->json(['message' =>$e->getMessage()] ,500);
        }
    }

    public function getCategoryTree(){
        try{
            $category = $this->categoryService->getCategoryTree();
            return response()->json([
                'message'=>'success',
                'category'=>$category
            ],200);
        }
        catch(\Exception $e){
            return response()->json(['message'=>$e->getMessage()] ,500);
        }
    }

    public function getLeafOnly(){
        try {
            $category = $this->categoryService->getLeafCategories();
            return response()->json([
                'message' => 'success',
                'category' => $category
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function getAllCategory(Request $request){
        try{
            $filters = $request->only('keyword');
            $category = $this->categoryService->getAllCategory($filters);
            return response()->json([
                'message'=>'succes',
                'category' =>$category
            ], 200);
        }
        catch(\Exception $e){
            return response()->json(['message'=>$e->getMessage()] ,500);
        }
    }

    public function reorder(Request $request){
        try{
            $this->categoryService->updateCategoryOrder($request->all());
            return response()->json(['message'=> 'Reoder success'],200);
        }
        catch(\Exception $e){
            return response()->json(['message'=>$e->getMessage()],500);
        }
    }

    public function getCategory(Category $category){
        try{
            $category = $this->categoryService->getCategory($category);
            return response()->json([
                'message'=>'success',
                'category'=>$category
            ],200);
        }
        catch(\Exception $e){
            return response()->json(['message'=>$e->getMessage()],500);
        }
    }

    public function getCategoryChildrenById($childrenId){
        try{
            $children = $this->categoryService->getCategoryChildrenById($childrenId);
            return response()->json([
                'message'=> 'success',
                'children'=> $children
            ] ,200);
        }catch(\Exception $e){
            return response()->json(['message'=>$e->getMessage()],500);
        }
    }

    public function getAllProductByChildrenCategoryTree($parentId){
        try{
            $product = $this->categoryService->getAllProductByChildrenCategoryTree($parentId);
            return response()->json([
                'message'=> 'success',
                'product'=> $product
            ],200);
        }catch(\Exception $e){
            return response()->json(['message'=>$e->getMessage()],500);
        }
    }

    public function editCategory(Request $request, Category $category){
        $validate = Validator::make($request->all(), [
            'name'=> 'required|string',
            'meta_title'=> 'required|string',
            'meta_description'=> 'required|string',
            'keywords'=>'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'banner' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);
        if($validate->fails()){
            return response()->json(['message' => $validate->errors()] ,500);
        }
        try{
            $data = $validate->validate();
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image');
            }
            if ($request->hasFile('banner')) {
                $data['banner'] = $request->file('banner');
            }
            $category = $this->categoryService->updateCategory($category, $data);
            return response()->json(['message'=> 'success','category'=>$category],200);
        }catch(\Exception $e){
            return response()->json(['message'=>$e->getMessage()],500);
        }
    }

    public function deleteCategory(Category $category){
        try{
            $this->categoryService->deleteCategory($category);
            return response()->json(['message'=> 'success'],200);
        }catch(\Exception $e){
            return response()->json(['message'=>$e->getMessage()],500);
        }
    }
}
