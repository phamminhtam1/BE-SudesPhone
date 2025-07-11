<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Product\CreateProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use Illuminate\Support\Facades\Validator;
use App\Models\Product;
use App\Services\ProductService;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService){
        $this->productService = $productService;
    }

    public function addNewProduct(CreateProductRequest $request)
    {
        try{
            $data = $request->validated();
            $files = $data['images']??[];
            $specs = $data['specs'] ?? [];
            unset($data['specs']);
            unset($data['images']);

            $product = $this->productService->createProduct($data, $files, $specs);
            return response()->json([
                'message'=> 'success',
                'product'=>$product,
            ],200);
        }catch(\Exception $e){
            return response()->json(['message'=>$e->getMessage()],500);
        }
    }

    public function getAllProduct(Request $request){
        try {
            $product = $this->productService->getAllProducts($request->query());
            return response()->json(['product' => $product], 200);
        } catch(\Exception $e) {
            return response()->json(['message'=> $e->getMessage()], 500);
        }
    }

    public function getProduct(Product $product){
        try{
            $product = $this->productService->getProduct($product);
            return response()->json(['product'=> $product],200);
        }catch(\Exception $e){
            return response()->json(['message'=> $e->getMessage()],500);
        }
    }

    public function getProductHotSale(){
        try{
            $product = $this->productService->getProductHotSale();
            return response()->json([
                'message' => 'success',
                'product'=> $product
            ], 200);
        }catch(\Exception $e){
            return response()->json(['message'=> $e->getMessage()],500);
        }
    }

    public function editProduct(UpdateProductRequest $request, Product $product){
        try{
            $data = $request->validated();
            $files = $data['images'] ?? [];
            $specs = $data['specs'] ?? [];
            unset($data['specs']);
            unset($data['images']);
            $product = $this->productService->updateProduct($product, $data, $files, $specs);
            return response()->json([
                'message'=> 'success',
                'product'=>$product,
            ],200);
        }catch(\Exception $e){
            return response()->json(['message'=>$e->getMessage()],500);
        }
    }

    public function updateProductStatus(Request $request, Product $product){
        $validate = Validator::make($request->all(), [
            'status'=> 'required|integer|in:0,1'
        ]);
        if($validate->fails()){
            return response()->json(['message'=> $validate->errors()], 403);
        }
        try{
            $product = $this->productService->updateProductStatus($product, $request->status);
            return response()->json([
                'message'=> 'success',
                'product'=>$product,
            ]);
        }catch(\Exception $e){
            return response()->json(['message'=>$e->getMessage()],500);
        }
    }

    public function deleteProduct(Product $product){
        try{
            $this->productService->deleteProduct($product);
            return response()->json(['message'=> 'success'],200);
        }catch(\Exception $e){
            return response()->json(['message'=>$e->getMessage()],500);
        }
    }
}
