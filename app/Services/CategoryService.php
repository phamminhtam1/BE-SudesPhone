<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CategoryService
{
    public function createCategory(array $data){
        try{
            DB::beginTransaction();
            $category = new Category();
            $category->parent_id = $data['parent_id']??null;
            $category->name = $data['name'];
            $category->meta_title = $data['meta_title'];
            $category->meta_description = $data['meta_description'];
            $category->keywords = $data['keywords'];
            $category->save();
            if(!empty($data['image']) && $data['image']->isValid()){
                $path = $data['image']->store("categories/{$category->id}/image", 's3');
                Storage::disk('s3')->setVisibility($path, 'public');
                $category->image = $this->getCloudFrontUrl($path);
                $category->save();
            }
            if(!empty($data['banner']) && $data['banner']->isValid()){
                $path = $data['banner']->store("categories/{$category->id}/banner",'s3');
                Storage::disk('s3')->setVisibility($path,'public');
                $category->banner = $this->getCloudFrontUrl($path);
                $category->save();
            }
            DB::commit();
            Cache::forget('category:tree');
            Cache::forget('category:leaf');
            Cache::forget('category:all');
            if ($category->parent_id) {
                $this->forgetProductsByCategoryCache($category->parent_id);
            }
            return $category;
        }
        catch(\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function getCategoryTree(){
        return Cache::remember('category:tree', 18000, function () {
            return Category::whereNull('parent_id')->with('children')->get();
        });
    }

    public function getAllCategory($filters = []){
        $cacheKey = 'category:all';
        if (!empty($filters['keyword'])) {
            $cacheKey .= ':keyword:' . md5($filters['keyword']);
        }
        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($filters) {
            $query = Category::query();
            if (!empty($filters['keyword'])) {
                $query->where('name', 'like', '%' . $filters['keyword'] . '%');
            }
            return $query->get();
        });
    }

    public function getLeafCategories()
        {
            return Cache::remember('category:leaf', 18000, function () {
                return Category::whereDoesntHave('children')->get();
            });
        }

    public function updateCategoryOrder(array $tree, $parentId = null)
    {
    foreach ($tree as $node) {
        Category::where('id', $node['id'])->update([
            'parent_id' => $parentId
        ]);

        if (isset($node['children']) && is_array($node['children'])) {
            // Đệ quy gọi lại với parentId mới
            $this->updateCategoryOrder($node['children'], $node['id']);
            }
        }
    }

    public function getCategory(Category $category){
        return $category;
    }

    public function getCategoryChildrenById($parentId){
        return Category::where('parent_id', $parentId)->with('children')->get();
    }

    public function updateCategory(Category $category, array $data){
        try{
            DB::beginTransaction();
            $category->name = $data['name'];
            $category->meta_title = $data['meta_title'];
            $category->meta_description = $data['meta_description'];
            $category->keywords = $data['keywords'];
            if (!empty($data['image']) && $data['image']->isValid()) {
                if (!empty($category->image)) {
                    try {
                        $oldPath = ltrim(parse_url($category->image, PHP_URL_PATH), '/');
                        if (Storage::disk('s3')->exists($oldPath)) {
                            Storage::disk('s3')->delete($oldPath);
                        }
                    } catch (\Exception $e) {
                        Log::warning("Không thể xoá ảnh image cũ: " . $e->getMessage());
                    }
                }

                $path = $data['image']->store("categories/{$category->id}/image", 's3');
                Storage::disk('s3')->setVisibility($path, 'public');
                $category->image = $this->getCloudFrontUrl($path);
            }
            if (!empty($data['banner']) && $data['banner']->isValid()) {
                if (!empty($category->banner)) {
                    try {
                        $oldPath = ltrim(parse_url($category->banner, PHP_URL_PATH), '/');
                        if (Storage::disk('s3')->exists($oldPath)) {
                            Storage::disk('s3')->delete($oldPath);
                        }
                    } catch (\Exception $e) {
                        Log::warning("Không thể xoá ảnh banner cũ: " . $e->getMessage());
                    }
                }

                $path = $data['banner']->store("categories/{$category->id}/banner", 's3');
                Storage::disk('s3')->setVisibility($path, 'public');
                $category->banner = $this->getCloudFrontUrl($path);
            }
            $category->save();
            DB::commit();
            Cache::forget('category:tree');
            Cache::forget('category:leaf');
            Cache::forget('category:all');
            $this->forgetProductsByCategoryCache($category->id);
            if ($category->parent_id) {
                $this->forgetProductsByCategoryCache($category->parent_id);
            }
            return $category;
        }catch(\Exception $e){
            DB::rollBack();
            return $e;
        }
    }

    public function deleteCategory(Category $category){
        try{
            DB::beginTransaction();
            foreach ($category->children as $child){
                $this->deleteCategory($child);
            }
            $parentId = $category->parent_id;
            $category->delete();
            DB::commit();
            Cache::forget('category:tree');
            Cache::forget('category:leaf');
            Cache::forget('category:all');
            $this->forgetProductsByCategoryCache($category->id);
            if ($parentId) {
                $this->forgetProductsByCategoryCache($parentId);
            }
            return true;
        }catch(\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    private function getCloudFrontUrl($path)
    {
        $cloudfront = rtrim(env('CLOUDFRONT_URL'), '/');
        $path = ltrim($path, '/');
        return $cloudfront . '/' . $path;
    }

    public function Recursion($parentId, &$ids){
        $children = Category::where('parent_id', $parentId)->get('id');
        foreach ($children as $child){
            $ids[] = $child->id;
            $this->Recursion($child->id, $ids);
        }
    }

    public function getAllChildrenCategoryTree($parentId){
        $ids = [];
        $this->Recursion($parentId, $ids);
        return $ids;
    }

    public function getAllProductByChildrenCategoryTree($parentId){
        $page = request('page', 1);
        $cacheKey = "products_by_category_{$parentId}_page_{$page}";
            $categoryIds = [$parentId];
            $categoryIds = array_merge($categoryIds, $this->getAllChildrenCategoryTree($parentId));
            return Product::whereIn('cat_id', $categoryIds)
                ->orderBy('created_at', 'desc')
                ->with(['images', 'specs'])
                ->paginate(30);
    }

    private function forgetProductsByCategoryCache($parentId)
    {
        // Xóa cache cho tất cả các trang (page)
        $categoryIds = [$parentId];
        $categoryIds = array_merge($categoryIds, $this->getAllChildrenCategoryTree($parentId));
        foreach ($categoryIds as $cid) {
            // Xóa cache cho các trang phổ biến (1-10), nếu cần nhiều hơn thì có thể mở rộng
            for ($page = 1; $page <= 10; $page++) {
                $cacheKey = "products_by_category_{$cid}_page_{$page}";
                Cache::forget($cacheKey);
            }
        }
    }
}
