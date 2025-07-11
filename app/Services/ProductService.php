<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductSpec;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

use function Termwind\parse;

class ProductService
{
    public function createProduct(array $data, array $files = [], array $specs = []){
        try{
            DB::beginTransaction();
            $product = new Product();
            $product->cat_id = $data['cat_id'];
            $product->name = $data['name'];
            $product->short_desc = $data['short_desc'];
            $product->long_desc = $data['long_desc'];
            $product->price = $data['price'];
            $product->discount_price = $data['discount_price'];
            $product->warranty_months = $data['warranty_months'];
            $product->stock_qty = $data['stock_qty']?? 0;
            $product->keywords = $data['keywords'];
            $product->save();
            if(!empty($product->sku)){
                $qrCode = QrCode::format('png')->size(300)->generate($product->sku);
                $qrPath = "products/{$product->prod_id}/qr_{$product->sku}.png";
                Storage::disk('s3')->put($qrPath, $qrCode);
                Storage::disk('s3')->setVisibility($qrPath,'public');
                $product->qr_path = $this->getCloudFrontUrl($qrPath);
                $product->save();
            }
            $sortOrder = 1;
            foreach($files as $file){
                if (!$file->isValid()) {
                    continue;
                }
                $path = $file->store("products/{$product->prod_id}", 's3');
                Storage::disk('s3')->setVisibility($path, 'public');
                $url = $this->getCloudFrontUrl($path);
                $product->images()->create([
                    'img_url'=> $url,
                    'alt_text'=>$product->name,
                    'sort_order'=>$sortOrder++,
                ]);
            }
            foreach ($specs as $spec) {
                ProductSpec::create([
                    'prod_id' => $product->prod_id,
                    'spec_key' => $spec['spec_key'],
                    'spec_value' => $spec['spec_value'],
                ]);
            }
            DB::commit();
            Cache::forget('product:hotsale');
            return $product->load(['images', 'specs']);
        }catch(\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function getAllProducts($filters = []){
        $perPage = isset($filters['per_page']) ? (int) $filters['per_page'] :20;
        $query = Product::query()
            ->with(['images', 'category:id,name,slug', 'specs']);
        if (!empty($filters['keyword'])) {
            $query->where('name', 'like', '%' . $filters['keyword'] . '%')
                ->orWhere('sku', 'like', '%' . $filters['keyword'] . '%');
        }
        if(!empty($filters['cat_id'])){
            $query->where('cat_id', $filters['cat_id']);
        }
        if(!empty($filters['spec_key'])&& !empty($filters['spec_value'])){
            $query->whereHas('specs', function($q) use ($filters){
                $q->where('spec_key', $filters['spec_key'])
                ->where('spec_value', $filters['spec_value']);
            });
        }
        if(!empty($filters['price_type'])){
            $priceColumn = $filters['price_type'];
            if(!empty($filters['min_price'])){
                $query->where($priceColumn, '>=', $filters['min_price']);
            }
            if(!empty($filters['max_price'])){
                $query->where($priceColumn, '<=', $filters['max_price']);
            }
        }
        return $query->orderByDesc('created_at')->paginate($perPage)->appends($filters);
    }

    public function getProduct(Product $product){
        return $product->load([
            'images',
            'category:id,name',
            'specs'
        ]);
    }

    public function getProductHotSale()
    {
        return Cache::remember('product:hotsale', 18000, function () {
            return Product::whereColumn('discount_price', '<', 'price')
                ->whereRaw('(price - discount_price)/price >= 0.3')
                ->where('status', '=', '1')
                ->with(['images', 'category:id,name,slug', 'specs'])
                ->get();
        });
    }

    public function updateProduct(Product $product, array $data, array $files, array $specs){
        try {
            DB::beginTransaction();
            $product->fill([
                'cat_id' => $data['cat_id'],
                'name' => $data['name'],
                'short_desc' => $data['short_desc'],
                'long_desc' => $data['long_desc'],
                'price' => $data['price'],
                'discount_price' => $data['discount_price'],
                'warranty_months' => $data['warranty_months'],
                'stock_qty' => $data['stock_qty'],
                'keywords' => $data['keywords'],
            ]);
            $product->save();
            if (!empty($files)) {
                foreach ($product->images as $image) {
                    try {
                        $path = ltrim(parse_url($image->img_url, PHP_URL_PATH), '/');
                        if (Storage::disk('s3')->exists($path)) {
                            Storage::disk('s3')->delete($path);
                        }
                    } catch (\Exception $e) {
                        Log::warning("Không thể xóa ảnh: " . $e->getMessage());
                    }
                    $image->delete();
                }
                $sortOrder = 1;
                foreach ($files as $file) {
                    $path = $file->store("products/{$product->prod_id}", 's3');
                    Storage::disk('s3')->setVisibility($path, 'public');
                    $url = $this->getCloudFrontUrl($path);
                    $product->images()->create([
                        'img_url' => $url,
                        'alt_text' => $product->name,
                        'sort_order' => $sortOrder++,
                    ]);
                }
            }
            if (!empty($specs)) {
                $product->specs()->delete();

                foreach ($specs as $spec) {
                    if (!empty($spec['spec_key']) && !empty($spec['spec_value'])) {
                        $product->specs()->create([
                            'spec_key' => $spec['spec_key'],
                            'spec_value' => $spec['spec_value'],
                        ]);
                    }
                }
            }
            DB::commit();
            Cache::forget('product:hotsale');
            return $product->load(['images', 'specs']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateProductStatus(Product $product, int $status){
        try{
            $product->status = $status;
            $product->save();
            Cache::forget('product:hotsale');
            return $product;
        }catch (\Exception $e) {
            throw $e;
        }
    }

    public function deleteProduct(Product $product){
        try{
            DB::beginTransaction();
            foreach($product->images as $image){
                try{
                    $path = ltrim(parse_url($image->img_url, PHP_URL_PATH), '/');
                    if(Storage::disk('s3')->exists($path)){
                        Storage::disk('s3')->delete($path);
                    }
                }catch(\Exception $e){
                    return response()->json(['message'=>$e->getMessage()],999);
                }
                $image->delete();
            }
            $product->delete();
            DB::commit();
            Cache::forget('product:hotsale');
            return true;
        }catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message'=>$e->getMessage()],888);
        }
    }

    private function getCloudFrontUrl($path)
    {
        $cloudfront = rtrim(env('CLOUDFRONT_URL'), '/');
        $path = ltrim($path, '/');
        return $cloudfront . '/' . $path;
    }

}
